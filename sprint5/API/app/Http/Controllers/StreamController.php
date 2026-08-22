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
     *      description="Streams a finite burst of simulated purchases built from real in-stock
     *          products, using the `text/event-stream` protocol. Named events are emitted in
     *          order: one `open`, then `sale` events (with an occasional `heartbeat`), then a
     *          final `end`. Each `sale` carries an incrementing SSE `id`, so a reconnecting
     *          client can send `Last-Event-ID` to resume numbering. Provide `seed` for a
     *          deterministic, repeatable sequence.",
     *      @OA\Parameter(name="limit", in="query", required=false,
     *          description="Number of sale events to emit before closing (1-50, default 10).",
     *          @OA\Schema(type="integer", example=10)),
     *      @OA\Parameter(name="interval", in="query", required=false,
     *          description="Delay between events in milliseconds (100-3000, default 1000).",
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

            // First bytes right away so the client opens the connection promptly.
            // The padding is a comment line (ignored by EventSource) sized to push
            // past the fixed buffer Apache/php-fpm/proxies fill before forwarding the
            // first chunk. 8 KB clears the common 4 KB/8 KB thresholds. If a proxy
            // buffers the whole response regardless, that must be fixed in server
            // config (see StreamController docblock / deploy notes), not here.
            echo ': ' . str_repeat(' ', 8192) . "\n\n";
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
            // connection is not left silent during the query.
            $catalog = DB::table('products')
                ->where('stock', '>', 0)
                ->orderBy('id')
                ->limit(100)
                ->get(['id', 'name', 'price']);

            $emit('open', [
                'started_at'   => now()->toIso8601String(),
                'limit'        => $limit,
                'interval_ms'  => $interval,
                'catalog_size' => $catalog->count(),
                'seeded'       => $hasSeed,
            ], $startId - 1);

            if ($catalog->isEmpty()) {
                $emit('end', ['count' => 0, 'total_amount' => 0.0, 'reason' => 'no products in stock']);
                return;
            }

            $count = $catalog->count();
            $runningTotal = 0.0;

            for ($i = 0; $i < $limit; $i++) {
                if (connection_aborted()) {
                    break;
                }

                $seq = $startId + $i;
                $product = $catalog[mt_rand(0, $count - 1)];
                $quantity = mt_rand(1, 3);
                $unitPrice = round((float)$product->price, 2);
                $amount = round($unitPrice * $quantity, 2);
                $runningTotal = round($runningTotal + $amount, 2);

                $emit('sale', [
                    'seq'           => $seq,
                    'at'            => now()->toIso8601String(),
                    'product_id'    => $product->id,
                    'name'          => $product->name,
                    'unit_price'    => $unitPrice,
                    'quantity'      => $quantity,
                    'amount'        => $amount,
                    'running_total' => $runningTotal,
                    'buyer'         => self::BUYERS[mt_rand(0, count(self::BUYERS) - 1)],
                    'city'          => self::CITIES[mt_rand(0, count(self::CITIES) - 1)],
                ], $seq);

                // A periodic heartbeat gives idle consumers something to react to
                // and keeps proxies from closing a quiet connection.
                if (($i + 1) % 5 === 0 && $i + 1 < $limit) {
                    $emit('heartbeat', ['at' => now()->toIso8601String(), 'emitted' => $i + 1]);
                }

                // Pace the feed. The sleep is at the end of the loop so the first
                // sale is sent immediately instead of after one interval.
                if ($i + 1 < $limit) {
                    usleep($interval * 1000);
                }
            }

            $emit('end', [
                'count'        => $limit,
                'total_amount' => $runningTotal,
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
