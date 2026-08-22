<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a live "sales feed" over Server-Sent Events (SSE).
 *
 * Each connection replays a short burst of simulated purchases built from real,
 * in-stock catalog products. The stream is finite (see the `limit` parameter) so
 * clients, workers and automated tests always terminate. Pass a `seed` to get a
 * fully deterministic sequence, which is handy for assertions in tests.
 */
class StreamController extends Controller
{
    /** Buyers and cities are cosmetic, only there to make the feed feel alive. */
    private const BUYERS = ['Alex', 'Sam', 'Robin', 'Jamie', 'Casey', 'Morgan', 'Riley', 'Jordan', 'Noor', 'Kai'];
    private const CITIES = ['Amsterdam', 'Berlin', 'Vienna', 'London', 'Utrecht', 'Frankfurt', 'Madrid', 'Oslo'];

    /**
     * @OA\Get(
     *      path="/sales-stream",
     *      operationId="salesStream",
     *      tags={"Stream"},
     *      summary="Live sales feed over Server-Sent Events (SSE)",
     *      description="Streams a finite run of simulated purchases built from real in-stock
     *          products, using the `text/event-stream` protocol. Named events are emitted in
     *          order: one `open`, then `sale` events (with an occasional `heartbeat`), then a
     *          final `end`. Each `sale` carries an incrementing SSE `id`, so a reconnecting
     *          client can send `Last-Event-ID` to resume numbering. Provide `seed` for a
     *          deterministic, repeatable sequence.
     *          The feed is modelled to feel alive: sales are **popularity-weighted** (cheaper,
     *          consumable items sell far more often than expensive ones), arrive in **bursts and
     *          lulls** rather than at a fixed rate, and are **stock-aware** - each sale draws down
     *          a per-stream stock counter (`remaining_stock`), quantities are capped to what is
     *          left, a product is retired once it hits zero (`sold_out: true`), and if everything
     *          sells out a `sold-out` event is emitted and the stream ends early.",
     *      @OA\Parameter(name="limit", in="query", required=false,
     *          description="Maximum number of sale events to emit before closing (1-50, default 10).
     *              Fewer may be emitted if all products sell out first.",
     *          @OA\Schema(type="integer", example=10)),
     *      @OA\Parameter(name="interval", in="query", required=false,
     *          description="Base delay between events in milliseconds (100-3000, default 1000).
     *              Within a burst the gaps are ~12-35% of this; between bursts ~90-260%, so the
     *              feed reads as organic rather than fixed-rate.",
     *          @OA\Schema(type="integer", example=1000)),
     *      @OA\Parameter(name="seed", in="query", required=false,
     *          description="Seed the random generator for a deterministic sequence.",
     *          @OA\Schema(type="integer", example=42)),
     *      @OA\Response(
     *          response=200,
     *          description="An SSE stream.",
     *          @OA\MediaType(
     *              mediaType="text/event-stream",
     *              @OA\Schema(type="string",
     *                  description="A sequence of SSE frames: an open event, then sale events (each with an id and a JSON payload), an occasional heartbeat, and a final end event.")
     *          )
     *      )
     * )
     */
    public function sales(Request $request): StreamedResponse
    {
        $limit = (int)$request->query('limit', 10);
        $limit = max(1, min(50, $limit));

        $interval = (int)$request->query('interval', 1000);
        $interval = max(100, min(3000, $interval));

        $seed = $request->query('seed');
        $hasSeed = $seed !== null && is_numeric($seed);

        // A reconnecting client may resume the id sequence via Last-Event-ID.
        $startId = 1;
        $lastEventId = $request->header('Last-Event-ID');
        if (is_numeric($lastEventId)) {
            $startId = ((int)$lastEventId) + 1;
        }

        // response()->stream() returns the same Symfony StreamedResponse the
        // eventStream() helper uses. The headers below, together with disabling
        // PHP output buffering here, are what make events arrive one by one
        // instead of all at once at the end. The product catalog is queried inside
        // the stream (after the first flush) so the first byte is not held back by
        // the database round-trip.
        return response()->stream(function () use ($limit, $interval, $hasSeed, $seed, $startId) {
            @set_time_limit(0);
            @ignore_user_abort(true);
            // Defeat every layer of PHP output buffering so each write is sent now.
            @ini_set('zlib.output_compression', '0');
            @ini_set('output_buffering', 'off');
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            @ob_implicit_flush(true);

            // First byte right away (a comment line, ignored by EventSource) so the
            // client sees the connection open immediately. Note: this does NOT defeat
            // a proxy that buffers the whole response - e.g. Apache mod_http2 on
            // mpm_prefork buffers regardless. That must be fixed in server config
            // (serve the stream over HTTP/1.1, or use mpm_event + php-fpm).
            echo ": ok\n\n";
            @flush();

            if ($hasSeed) {
                mt_srand((int)$seed);
            }

            $emit = function (string $event, array $data, ?int $id = null) {
                if ($id !== null) {
                    echo "id: {$id}\n";
                }
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
                @ob_flush();
                @flush();
            };

            // Tell the browser how quickly to reconnect if the connection drops.
            echo 'retry: ' . $interval . "\n\n";
            @flush();

            // Stable, ordered catalog so a given seed always yields the same picks.
            // Queried here (after the opening bytes are already on the wire) so the
            // connection is not left silent during the query. `stock` drives the
            // stock-awareness below.
            $catalog = DB::table('products')
                ->where('stock', '>', 0)
                ->orderBy('id')
                ->limit(100)
                ->get(['id', 'name', 'price', 'stock']);

            // Build the sellable set with a local stock counter and a popularity
            // weight. Cheaper, consumable items (washers, bolts) sell far more often
            // than an expensive circular saw: weight rises as price falls.
            $products = [];
            foreach ($catalog as $p) {
                $price = round((float)$p->price, 2);
                $products[] = (object)[
                    'id'     => $p->id,
                    'name'   => $p->name,
                    'price'  => $price,
                    'stock'  => (int)$p->stock,
                    'weight' => max(1, (int)round(1000 / (max($price, 1.0) + 5))),
                ];
            }

            $emit('open', [
                'started_at'   => now()->toIso8601String(),
                'limit'        => $limit,
                'interval_ms'  => $interval,
                'catalog_size' => count($products),
                'seeded'       => $hasSeed,
            ], $startId - 1);

            if (empty($products)) {
                $emit('end', ['count' => 0, 'total_amount' => 0.0, 'reason' => 'no products in stock']);
                return;
            }

            $runningTotal = 0.0;
            $emitted = 0;
            $soldOut = 0;
            $burstLeft = 0; // >0 while a burst of quick, back-to-back sales is running

            for ($i = 0; $i < $limit; $i++) {
                if (connection_aborted()) {
                    break;
                }

                // Popularity-weighted pick among products that still have stock.
                $totalWeight = 0;
                foreach ($products as $p) {
                    $totalWeight += $p->weight;
                }
                if ($totalWeight <= 0) {
                    $emit('sold-out', ['at' => now()->toIso8601String(), 'message' => 'every product is sold out']);
                    break;
                }
                $r = mt_rand(1, $totalWeight);
                $product = $products[array_key_last($products)];
                foreach ($products as $p) {
                    $r -= $p->weight;
                    if ($r <= 0) {
                        $product = $p;
                        break;
                    }
                }

                // Weighted quantity, capped to whatever stock is left.
                $roll = mt_rand(1, 100);
                $wantQty = $roll <= 55 ? 1 : ($roll <= 82 ? 2 : ($roll <= 94 ? 3 : ($roll <= 99 ? 4 : 5)));
                $quantity = min($wantQty, $product->stock);

                $unitPrice = $product->price;
                $amount = round($unitPrice * $quantity, 2);
                $runningTotal = round($runningTotal + $amount, 2);

                // Draw down the local stock; retire the product once it is sold out
                // so it stops being offered for the rest of this stream.
                $product->stock -= $quantity;
                $justSoldOut = $product->stock <= 0;
                if ($justSoldOut) {
                    $product->weight = 0;
                    $soldOut++;
                }

                $seq = $startId + $emitted;
                $emit('sale', [
                    'seq'             => $seq,
                    'at'              => now()->toIso8601String(),
                    'product_id'      => $product->id,
                    'name'            => $product->name,
                    'unit_price'      => $unitPrice,
                    'quantity'        => $quantity,
                    'amount'          => $amount,
                    'running_total'   => $runningTotal,
                    'remaining_stock' => max(0, $product->stock),
                    'sold_out'        => $justSoldOut,
                    'buyer'           => self::BUYERS[mt_rand(0, count(self::BUYERS) - 1)],
                    'city'            => self::CITIES[mt_rand(0, count(self::CITIES) - 1)],
                ], $seq);
                $emitted++;

                // A periodic heartbeat gives idle consumers something to react to
                // and keeps proxies from closing a quiet connection.
                if ($emitted % 5 === 0 && $i + 1 < $limit) {
                    $emit('heartbeat', ['at' => now()->toIso8601String(), 'emitted' => $emitted]);
                }

                // Pace the feed as bursts and lulls: while a burst is running the
                // gaps are short (quick back-to-back sales); between bursts there is
                // a longer pause, with a chance to kick off the next burst. mt_rand
                // keeps it reproducible when a seed is given.
                if ($i + 1 < $limit) {
                    if ($burstLeft > 0) {
                        $burstLeft--;
                        $gapMs = (int)round($interval * mt_rand(12, 35) / 100);
                    } else {
                        $gapMs = (int)round($interval * mt_rand(90, 260) / 100);
                        if (mt_rand(1, 100) <= 35) {
                            $burstLeft = mt_rand(1, 2);
                        }
                    }
                    usleep($gapMs * 1000);
                }
            }

            $emit('end', [
                'count'        => $emitted,
                'total_amount' => $runningTotal,
                'sold_out'     => $soldOut,
                'ended_at'     => now()->toIso8601String(),
            ]);
        }, 200, [
            'Content-Type'      => 'text/event-stream; charset=UTF-8',
            'Cache-Control'     => 'no-cache, no-transform',
            'Connection'        => 'keep-alive',
            // Tell nginx (and other proxies) not to buffer this response.
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
