<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:admin');
    }

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
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function totalSalesPerCountry()
    {
        $results = DB::table('invoices')
            ->selectRaw('SUM(total) as "total_sales", billing_country')
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
            ->selectRaw('p.name, count(p.name) as count')
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
        $results = DB::table('categories AS c')
            ->join('products AS p', 'p.category_id', '=', 'c.id')
            ->join('invoice_items AS i', 'i.product_id', '=', 'p.id')
            ->selectRaw('c.name as category_name, SUM(i.unit_price) as total_earned')
            ->groupBy('c.name')
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
        $numberOfYears = $request->get('years', 1);
        $endYear = now()->year;
        $startYear = $endYear - $numberOfYears;

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

        $formattedResults = $this->formatYearlySalesData($results, $startYear, $endYear);

        return $this->preferredFormat($formattedResults);
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
        $year = $request->get('year', now()->year);

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

        $formattedResults = $this->formatMonthlySalesData($results);

        return $this->preferredFormat($formattedResults);
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
        $year = $request->get('year', now()->year);

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

        $formattedResults = $this->formatWeeklySalesData($results);

        return $this->preferredFormat($formattedResults);
    }

    /**
     * @OA\Get(
     *      path="/reports/customers-by-country",
     *      operationId="getCustomersByCountry",
     *      tags={"Report"},
     *      summary="Get customers by country",
     *      description="`Admin` role is required to get customers by country",
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

    private function formatYearlySalesData($results, $startYear, $endYear)
    {
        $formattedResults = [];
        $yearlyData = [];

        foreach ($results as $result) {
            $yearlyData[$result->year] = [
                'year' => $result->year,
                'total' => floatval($result->total)
            ];
        }

        for ($year = $startYear; $year <= $endYear; $year++) {
            if (array_key_exists($year, $yearlyData)) {
                $formattedResults[] = $yearlyData[$year];
            } else {
                $formattedResults[] = ['year' => $year, 'total' => 0];
            }
        }

        return $formattedResults;
    }

    private function formatMonthlySalesData($results)
    {
        $formattedResults = [];
        $monthlyData = [];

        foreach ($results as $result) {
            $monthlyData[$result->month] = [
                'month' => $result->month,
                'average' => floatval($result->average),
                'amount' => floatval($result->amount)
            ];
        }

        for ($month = 1; $month <= 12; $month++) {
            if (array_key_exists($month, $monthlyData)) {
                $formattedResults[] = $monthlyData[$month];
            } else {
                $formattedResults[] = ['month' => $month, 'average' => 0, 'amount' => 0];
            }
        }

        return $formattedResults;
    }

    private function formatWeeklySalesData($results)
    {
        $formattedResults = [];
        $weeklyData = [];

        foreach ($results as $result) {
            $weeklyData[$result->week] = [
                'week' => $result->week,
                'average' => floatval($result->average),
                'amount' => floatval($result->amount)
            ];
        }

        for ($week = 1; $week <= 52; $week++) {
            if (array_key_exists($week, $weeklyData)) {
                $formattedResults[] = $weeklyData[$week];
            } else {
                $formattedResults[] = ['week' => $week, 'average' => 0, 'amount' => 0];
            }
        }

        return $formattedResults;
    }

}
