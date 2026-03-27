<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     *                  title="TotalSalesPerCountryResponse",
     *                  @OA\Property(
     *                      property="billing_country",
     *                      type="string",
     *                      example="The Netherlands",
     *                      description="The billing country"
     *                  ),
     *                  @OA\Property(
     *                      property="total_sales",
     *                      type="string",
     *                      example="1234",
     *                      description="Total sales in the country"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function totalSalesPerCountry()
    {
        Log::info('Fetching total sales per country');

        $results = DB::table('invoices')
            ->select(DB::raw('SUM(total) as "total_sales", billing_country'))
            ->groupBy('billing_country')
            ->get();

        Log::debug('Total sales per country retrieved', ['count' => $results->count()]);

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
     *           response=200,
     *           description="Successful operation",
     *           @OA\JsonContent(
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *                   title="TopPurchasedProductsResponse",
     *                   @OA\Property(
     *                       property="name",
     *                       type="string",
     *                       example="item",
     *                       description="Name of the product"
     *                   ),
     *                   @OA\Property(
     *                       property="count",
     *                       type="integer",
     *                       example=2,
     *                       description="Number of times the product was purchased"
     *                   )
     *               )
     *           )
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function top10PurchasedProducts()
    {
        Log::info('Fetching top 10 purchased products');

        $results = DB::table('products AS p')
            ->join('invoice_items AS i', 'i.product_id', '=', 'p.id')
            ->select(DB::raw('p.name, count(p.name) as count'))
            ->groupBy('p.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        Log::debug('Top purchased products retrieved', ['count' => $results->count()]);

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
     *           response=200,
     *           description="Successful operation",
     *           @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                 type="object",
     *                 title="TopSellingCategoriesResponse",
     *                 @OA\Property(
     *                    property="category_name",
     *                    type="string",
     *                    example="item",
     *                    description="Name of the category"
     *                  ),
     *                  @OA\Property(
     *                    property="total_earned",
     *                    type="string",
     *                    example="1234",
     *                    description="Total earnings from this category"
     *                  )
     *              )
     *          )
     *       ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function top10BestSellingCategories()
    {
        Log::info('Fetching top 10 best selling categories');

        $results = DB::table(DB::raw('(SELECT c.name as "category_name", SUM(i.unit_price) as "total_earned"
                FROM invoice_items i
                JOIN products p ON p.id = i.product_id
                JOIN categories c ON p.category_id = c.id
                GROUP BY c.name) as f'))
            ->select(DB::raw('category_name, total_earned'))
            ->orderByDesc('total_earned')
            ->limit(10)
            ->get();

        Log::debug('Best selling categories retrieved', ['count' => $results->count()]);

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
     *           response=200,
     *           description="Successful operation",
     *           @OA\JsonContent(
     *               type="array",
     *               @OA\Items(
     *                  type="object",
     *                  title="TotalSalesOfYearsResponse",
     *                   @OA\Property(
     *                      property="year",
     *                      type="integer",
     *                      example=2022,
     *                      description="Year of the sales data"
     *                   ),
     *                   @OA\Property(
     *                      property="total",
     *                      type="number",
     *                      example=2,
     *                      description="Total sales for the given year"
     *                   )
     *               )
     *           )
     *       ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function totalSalesOfYears(Request $request)
    {
        $years = $request->get('years', 1);
        Log::info('Fetching total sales of past years', ['years' => $years]);

        $numberOfYears = $years - 1;
        $endYear = intval(date("Y"));
        $startYear = $endYear - $numberOfYears;

        $driver = config('database.default');
        Log::debug('Using DB driver', ['driver' => $driver]);

        if ($driver == 'sqlite') {
            $yearQuery = "strftime('%Y', invoice_date) AS year";
            $yearGroup = "strftime('%Y', invoice_date)";
        } else {
            $yearQuery = 'YEAR(invoice_date) AS year';
            $yearGroup = 'YEAR(invoice_date)';
        }

        $results = DB::table('invoices')
            ->selectRaw("SUM(total) AS total, $yearQuery")
            ->whereYear('invoice_date', '>=', $startYear)
            ->groupBy(DB::raw($yearGroup))
            ->get();

        Log::debug('Sales by year retrieved', ['years' => $results->pluck('year')->toArray()]);

        $dates = [];
        foreach ($results as $result) {
            $dates[$result->year] = floatval($result->total);
        }

        $rs = [];
        for ($i = $startYear; $i <= $endYear; $i++) {
            $rs[] = [
                'year' => $i,
                'total' => $dates[$i] ?? 0
            ];
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
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 title="AverageSalesPerMonthResponse",
     *                 @OA\Property(
     *                    property="month",
     *                    type="integer",
     *                    example=1,
     *                    description="Month number (1 for January, 12 for December)"
     *                 ),
     *                 @OA\Property(
     *                    property="average",
     *                    type="number",
     *                    example=2,
     *                    description="Average number of sales for the month"
     *                 ),
     *                 @OA\Property(
     *                    property="amount",
     *                    type="number",
     *                    format="float",
     *                    example=9.99,
     *                    description="Average sales amount for the month"
     *                )
     *            )
     *        )
     *    ),
     *    @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *    @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *    security={{ "apiAuth": {} }}
     * )
     */
    public function averageSalesPerMonth(Request $request)
    {
        $year = $request->get('year', date('Y'));
        Log::info('Fetching average sales per month', ['year' => $year]);

        $driver = config('database.default');
        Log::debug('Using DB driver', ['driver' => $driver]);

        if ($driver == 'sqlite') {
            $monthQuery = 'CAST(strftime("%m", invoice_date) AS INTEGER) AS month';
            $monthGroup = 'strftime("%m", invoice_date)';
        } else {
            $monthQuery = 'MONTH(invoice_date) AS month';
            $monthGroup = 'MONTH(invoice_date)';
        }

        $results = DB::table('invoices')
            ->selectRaw("$monthQuery, AVG(total) AS average, COUNT(*) AS amount")
            ->whereYear('invoice_date', '=', $year)
            ->groupBy(DB::raw($monthGroup))
            ->get();

        Log::debug('Monthly sales data retrieved', ['months' => $results->pluck('month')->toArray()]);

        $dates = [];
        foreach ($results as $result) {
            $dates[$result->month]['avg'] = floatval($result->average);
            $dates[$result->month]['amount'] = floatval($result->amount);
        }

        $rs = [];
        for ($i = 1; $i <= 12; $i++) {
            $rs[] = [
                'month' => $i,
                'average' => $dates[$i]['avg'] ?? 0,
                'amount' => $dates[$i]['amount'] ?? 0,
            ];
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
     *           response=200,
     *           description="Successful operation",
     *           @OA\JsonContent(
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *                   title="AverageSalesPerWeekResponse",
     *                   @OA\Property(
     *                      property="week",
     *                      type="integer",
     *                      example=1,
     *                      description="Week number of the year (1-52)"
     *                   ),
     *                   @OA\Property(
     *                      property="average",
     *                      type="number",
     *                      example=2,
     *                      description="Average number of sales for the week"
     *                   ),
     *                   @OA\Property(
     *                      property="amount",
     *                      type="number",
     *                      format="number",
     *                      example=9.99,
     *                      description="Average sales amount for the week"
     *                  )
     *              )
     *          )
     *       ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function averageSalesPerWeek(Request $request)
    {
        $year = $request->get('year', date('Y'));
        Log::info('Fetching average sales per week', ['year' => $year]);

        $driver = config('database.default');
        Log::debug('Using DB driver', ['driver' => $driver]);

        if ($driver == 'sqlite') {
            $weekQuery = 'CAST(strftime("%W", invoice_date) AS INTEGER) AS week';
            $weekGroup = 'strftime("%W", invoice_date)';
        } else {
            $weekQuery = 'WEEK(invoice_date) AS week';
            $weekGroup = 'WEEK(invoice_date)';
        }

        $results = DB::table('invoices')
            ->selectRaw("$weekQuery, AVG(total) AS average, COUNT(*) AS amount")
            ->whereYear('invoice_date', '=', $year)
            ->groupBy(DB::raw($weekGroup))
            ->get();

        Log::debug('Weekly sales data retrieved', ['weeks' => $results->pluck('week')->toArray()]);

        $dates = [];
        foreach ($results as $result) {
            $dates[$result->week]['avg'] = floatval($result->average);
            $dates[$result->week]['amount'] = floatval($result->amount);
        }

        $rs = [];
        for ($i = 1; $i <= 52; $i++) {
            $rs[] = [
                'week' => $i,
                'average' => $dates[$i]['avg'] ?? 0,
                'amount' => $dates[$i]['amount'] ?? 0,
            ];
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
     *           response=200,
     *           description="Successful operation",
     *           @OA\JsonContent(
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *                   title="CustomersByCountryResponse",
     *                   @OA\Property(
     *                       property="amount",
     *                       type="integer",
     *                       example=1,
     *                       description="Amount of customers"
     *                   ),
     *                   @OA\Property(
     *                       property="country",
     *                       type="string",
     *                       example="The Netherlands",
     *                       description="Country"
     *                   )
     *               )
     *           )
     *       ),
     *      @OA\Response(response="401", ref="#/components/responses/UnauthorizedResponse"),
     *      @OA\Response(response="404", ref="#/components/responses/ItemNotFoundResponse"),
     *      security={{ "apiAuth": {} }}
     * )
     */
    public function customersByCountry(Request $request)
    {
        Log::info('Fetching customers by country');

        $results = DB::table('users AS u')
            ->selectRaw('COUNT(*) AS amount, u.country')
            ->where('u.role', '=', 'user')
            ->groupBy('u.country')
            ->get();

        Log::debug('Customer distribution retrieved', ['countries' => $results->pluck('country')->toArray()]);

        return $this->preferredFormat($results);
    }

}
