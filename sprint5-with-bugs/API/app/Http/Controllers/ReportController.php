<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    /**
     * @OA\Get(
     *      path="/reports/total-sales-per-country",
     *      operationId="getTotalSalesPerCountry",
     *      tags={"Report"},
     *      summary="Get total sales per country",
     *      description="`Admin` role is required to get total sales per country",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  example={
     *                      "total_sales": 9.99,
     *                      "billing_country": "The Netherlands",
     *                  },
     *              )
     *          ),
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function totalSalesPerCountry()
    {
        $results = DB::table('invoices')
            ->select(DB::raw('SUM(total) as "total_sales", billing_country'))
            ->groupBy('billing_country')
            ->get();

        return $this->preferredFormat($results);
    }

    /**
     * @OA\Get(
     *      path="/reports/top10-purchased-products",
     *      operationId="getTopPurchasedProducts",
     *      tags={"Report"},
     *      summary="Get top 10 purchased products",
     *      description="`Admin` role is required to get top 10 purchased products",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  example={
     *                      "name": "item",
     *                      "count": 2,
     *                  },
     *              )
     *          ),
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function top10PurchasedProducts()
    {
        $results = DB::table('products AS p')
            ->join('invoice_items AS i', 'i.product_id', '=', 'p.id')
            ->select(DB::raw('p.name, count(p.name) as count'))
            ->groupBy('p.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return $this->preferredFormat($results);
    }

    /**
     * @OA\Get(
     *      path="/reports/top10-best-selling-categories",
     *      operationId="getBestSellingCategories",
     *      tags={"Report"},
     *      summary="Get top 10 best selling categories",
     *      description="`Admin` role is required to get top 10 best selling categories",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  example={
     *                      "category_name": "item",
     *                      "total_earned": 2,
     *                  },
     *              )
     *          ),
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function top10BestSellingCategories()
    {
        $results = DB::table(DB::raw('(SELECT c.name as "category_name", SUM(i.unit_price) as "total_earned"
                FROM invoice_items i
                JOIN products p
                ON p.id = i.product_id
                JOIN categories c
                ON p.category_id = c.id
                GROUP BY c.name) as f'))
            ->select(DB::raw('category_name, total_earned'))
            ->orderByDesc('total_earned')
            ->limit(10)
            ->get();

        return $this->preferredFormat($results);
    }

    /**
     * @OA\Get(
     *      path="/reports/total-sales-of-years",
     *      operationId="getTotalSalesOfYears",
     *      tags={"Report"},
     *      summary="Get total sales of years",
     *      description="`Admin` role is required to get total sales of years",
     *      @OA\Parameter(
     *          name="years",
     *          in="query",
     *          description="Number of years",
     *          required=false,
     *          example=2,
     *          @OA\Schema(type="integer", default=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  example={
     *                      "year": 2022,
     *                      "total": 2,
     *                  },
     *              )
     *          ),
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function totalSalesOfYears(Request $request)
    {
        $numberOfYears = $request->get('years', 1) - 1;
        $endYear = intval(date("Y"));
        $startYear = date("Y") - $numberOfYears;

        $driver = config('database.default');
        if ($driver == 'sqlite') {
            $yearQuery = "strftime('%Y', invoice_date) AS year";
            $yearGroup = "strftime('%Y', invoice_date)";
        } elseif ($driver == 'mysql') {
            $yearQuery = 'YEAR(invoice_date) AS year';
            $yearGroup = 'YEAR(invoice_date)';
        }

        $results = DB::table('invoices')
            ->selectRaw("SUM(total) AS total, $yearQuery")
            ->whereYear('invoice_date', '>=', $startYear)
            ->groupBy(DB::raw($yearGroup))
            ->get();

        $dates = [];
        foreach ($results as $result) {
            $dates[$result->year] = floatval($result->total);
        }

        for ($i = $startYear; $i <= $endYear; $i++) {
            $item['year'] = $i;
            $item['total'] = $dates[$i] ?? 0;
            $rs[] = $item;
        }
        return $this->preferredFormat($rs);
    }

    /**
     * @OA\Get(
     *      path="/reports/average-sales-per-month",
     *      operationId="getAverageSalesPerMonth",
     *      tags={"Report"},
     *      summary="Get average sales per month",
     *      description="`Admin` role is required to get average sales per month",
     *      @OA\Parameter(
     *          name="year",
     *          in="query",
     *          description="Specific year",
     *          required=false,
     *          example=2021,
     *          @OA\Schema(type="integer", default=2022)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  example={
     *                      "month": 1,
     *                      "average": 2,
     *                      "amount": 9.99
     *                  },
     *              )
     *          ),
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function averageSalesPerMonth(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $driver = config('database.default');
        if ($driver == 'sqlite') {
            $monthQuery = 'CAST(strftime("%m", invoice_date) AS INTEGER) AS month';
            $monthGroup = 'strftime("%m", invoice_date)';
        } elseif ($driver == 'mysql') {
            $monthQuery = 'MONTH(invoice_date) AS month';
            $monthGroup = 'MONTH(invoice_date)';
        }

        $results = DB::table('invoices')
            ->selectRaw("$monthQuery, AVG(total) AS average, COUNT(*) AS amount")
            ->whereYear('invoice_date', '=', $year)
            ->groupBy(DB::raw($monthGroup))
            ->get();

        $dates = [];
        foreach ($results as $result) {
            $dates[$result->month]['avg'] = floatval($result->average);
            $dates[$result->month]['amount'] = floatval($result->amount);
        }

        for ($i = 1; $i <= 12; $i++) {
            $item['month'] = $i;
            $item['average'] = $dates[$i]['avg'] ?? 0;
            $item['amount'] = $dates[$i]['amount'] ?? 0;
            $rs[] = $item;
        }
        return $this->preferredFormat($rs);
    }

    /**
     * @OA\Get(
     *      path="/reports/average-sales-per-week",
     *      operationId="getAverageSalesPerWeek",
     *      tags={"Report"},
     *      summary="Get average sales per week",
     *      description="`Admin` role is required to get average sales per week",
     *      @OA\Parameter(
     *          name="year",
     *          in="query",
     *          description="Specific year",
     *          required=false,
     *          example=2021,
     *          @OA\Schema(type="integer", default=2022)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  example={
     *                      "week": 1,
     *                      "average": 2,
     *                      "amount": 9.99
     *                  },
     *              )
     *          ),
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function averageSalesPerWeek(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $driver = config('database.default');
        if ($driver == 'sqlite') {
            $weekQuery = 'CAST(strftime("%W", invoice_date) AS INTEGER) AS week';
            $weekGroup = 'strftime("%W", invoice_date)';
        } elseif ($driver == 'mysql') {
            $weekQuery = 'WEEK(invoice_date) AS week';
            $weekGroup = 'WEEK(invoice_date)';
        }

        $results = DB::table('invoices')
            ->selectRaw("$weekQuery, AVG(total) AS average, COUNT(*) AS amount")
            ->whereYear('invoice_date', '=', $year)
            ->groupBy(DB::raw($weekGroup))
            ->get();

        $dates = [];
        foreach ($results as $result) {
            $dates[$result->week]['avg'] = floatval($result->average);
            $dates[$result->week]['amount'] = floatval($result->amount);
        }

        for ($i = 1; $i <= 52; $i++) {
            $item['week'] = $i;
            $item['average'] = $dates[$i]['avg'] ?? 0;
            $item['amount'] = $dates[$i]['amount'] ?? 0;
            $rs[] = $item;
        }
        return $this->preferredFormat($rs);
    }

    /**
     * @OA\Get(
     *      path="/reports/customers-by-country",
     *      operationId="getCustomersByCountry",
     *      tags={"Report"},
     *      summary="Get customers by country",
     *      description="`Admin` role is required to get customers by country",
     *      @OA\Parameter(
     *          name="country",
     *          in="query",
     *          description="Specific year",
     *          required=false,
     *          example=2021,
     *          @OA\Schema(type="string", default="The Netherlands")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  example={
     *                      "customer": "Jane Doe",
     *                      "country": "The Netherlands"
     *                  },
     *              )
     *          ),
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function customersByCountry(Request $request)
    {
        $results = DB::table('users AS u')
            ->selectRaw('COUNT(*) AS amount, u.country')
            ->where('u.role', '=', 'user')
            ->groupBy('u.country')
            ->get();

        return $this->preferredFormat($results);
    }

}
