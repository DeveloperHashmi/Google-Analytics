<?php
    // Load the Google API PHP Client Library.
    require_once __DIR__ . '/vendor/autoload.php';

    $analytics = initializeAnalytics();
    segmentRequest($analytics);
    


    /**
     * Initializes an Analytics Reporting API V4 service object.
     *
     * @return An authorized Analytics Reporting API V4 service object.
     */
    function initializeAnalytics()
    {

        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        $KEY_FILE_LOCATION = __DIR__ . '/zinc-reason-296212-52ce75836e9c.json';

        // Create and configure a new client object.
        $client = new Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new Google_Service_AnalyticsReporting($client);

        return $analytics;
    }
    
    function segmentRequest($analyticsreporting) {

        // Replace with your view ID, for example XXXX.
        $VIEW_ID = "233576869";

        // Create the DateRange object.
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("7daysAgo");
        $dateRange->setEndDate("today");
        
        // Create the Metrics object.
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:sessions");
        $sessions->setAlias("sessions");




        // $users = new Google_Service_AnalyticsReporting_Metric();
        // $users->setExpression("ga:users");
        // $users->setAlias("users");
        
        // $organicSearches = new Google_Service_AnalyticsReporting_Metric();
        // $organicSearches->setExpression("ga:organicSearches");
        // $organicSearches->setAlias("organicSearches");






        //Create the browser dimension.
        // $browser = new Google_Service_AnalyticsReporting_Dimension();
        // $browser->setName("ga:browser");

        $pagePath = new Google_Service_AnalyticsReporting_Dimension();
        $pagePath->setName("ga:pagePath");

        // Create the segment dimension.
        $segmentDimensions = new Google_Service_AnalyticsReporting_Dimension();
        $segmentDimensions->setName("ga:segment");

        // Create Dimension Filter.
        $dimensionFilter = new Google_Service_AnalyticsReporting_SegmentDimensionFilter();
        $dimensionFilter->setDimensionName("ga:pagePath");
        $dimensionFilter->setOperator("IN_LIST");
        $dimensionFilter->setExpressions(array("/webar/?id=markerless-experience","/webar/?id=wall-e"));

        // Create Segment Filter Clause.
        $segmentFilterClause = new Google_Service_AnalyticsReporting_SegmentFilterClause();
        $segmentFilterClause->setDimensionFilter($dimensionFilter);

        // Create the Or Filters for Segment.
        $orFiltersForSegment = new Google_Service_AnalyticsReporting_OrFiltersForSegment();
        $orFiltersForSegment->setSegmentFilterClauses(array($segmentFilterClause));

        // Create the Simple Segment.
        $simpleSegment = new Google_Service_AnalyticsReporting_SimpleSegment();
        $simpleSegment->setOrFiltersForSegment(array($orFiltersForSegment));

        // Create the Segment Filters.
        $segmentFilter = new Google_Service_AnalyticsReporting_SegmentFilter();
        $segmentFilter->setSimpleSegment($simpleSegment);

        // Create the Segment Definition.
        $segmentDefinition = new Google_Service_AnalyticsReporting_SegmentDefinition();
        $segmentDefinition->setSegmentFilters(array($segmentFilter));

        // Create the Dynamic Segment.
        $dynamicSegment = new Google_Service_AnalyticsReporting_DynamicSegment();
        $dynamicSegment->setSessionSegment($segmentDefinition);
        $dynamicSegment->setName("Sessions with URL ");

        // Create the Segments object.
        $segment = new Google_Service_AnalyticsReporting_Segment();
        $segment->setDynamicSegment($dynamicSegment);

        // Create the ReportRequest object.
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($VIEW_ID);
        $request->setDateRanges(array($dateRange));
        $request->setDimensions(array($segmentDimensions, $pagePath ));
        $request->setSegments(array($segment));
        $request->setMetrics(array($sessions));

        // Create the GetReportsRequest object.
        $getReport = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $getReport->setReportRequests(array($request));

        // Call the batchGet method.
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        $response = $analyticsreporting->reports->batchGet( $body );
        echo "<pre>";
        printResults($response->getReports());
    }

    /**
     * Parses and prints the Analytics Reporting API V4 response.
     *
     * @param An Analytics Reporting API V4 response.
     */
    function printResults($reports) {
        for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
            $report = $reports[ $reportIndex ];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();

            for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[ $rowIndex ];
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();
                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                    print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
                }

                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        print($entry->getName() . ": " . $values[$k] . "\n");
                    }
                }
            }
        }
    }