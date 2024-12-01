<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    private $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
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
        $results = $this->reportService->getTotalSalesPerCountry();
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
        $results = $this->reportService->getTop10PurchasedProducts();
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
        $results = $this->reportService->getTop10BestSellingCategories();
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
        $results = $this->reportService->getTotalSalesOfYears($startYear, $endYear, $driver);

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
        $results = $this->reportService->getAverageSalesPerMonth($year, $driver);

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
        $results = $this->reportService->getAverageSalesPerWeek($year, $driver);

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
        $results = $this->reportService->getCustomersByCountry();
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
