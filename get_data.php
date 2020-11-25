<?php

    // Load the Google API PHP Client Library.
    require_once __DIR__ . '/vendor/autoload.php';

    $analytics = initializeAnalytics();
    $response = getReport($analytics);
    echo "<pre>";
    printResults($response);


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


    /**
     * Queries the Analytics Reporting API V4.
     *
     * @param service An authorized Analytics Reporting API V4 service object.
     * @return The Analytics Reporting API V4 response.
     */
    function getReport($analytics) {

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
        
        $users = new Google_Service_AnalyticsReporting_Metric();
        $users->setExpression("ga:users");
        $users->setAlias("users");
        
        $organicSearches = new Google_Service_AnalyticsReporting_Metric();
        $organicSearches->setExpression("ga:organicSearches");
        $organicSearches->setAlias("organicSearches");

        //Create the Dimensions object.
        $browser = new Google_Service_AnalyticsReporting_Dimension();
        $browser->setName("ga:browser");
        
        $source = new Google_Service_AnalyticsReporting_Dimension();
        $source->setName("ga:sourceMedium");
        
        $pagePath = new Google_Service_AnalyticsReporting_Dimension();
        $pagePath->setName("ga:pagePath");


        // Create the ReportRequest object.
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($VIEW_ID);
        $request->setDateRanges($dateRange);
        $request->setDimensions(array($browser,$source,$pagePath));
        $request->setMetrics(array($sessions,$users,$organicSearches));

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        return $analytics->reports->batchGet( $body );
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
