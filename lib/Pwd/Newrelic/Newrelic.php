<?php
/**
 * New relic php API adapter class to avoid calling Global functions
 *
 * Also guarding native calls by checking the the new relic extension is loaded
 */

//namespace Pwd\Newrelic;

class Pwd_Newrelic_Newrelic
{

    //singleton
    private static $instance;

    //flag to show if the extension is loaded
    protected $loaded = false;

    protected function __construct($ignore = true)
    {
        if (extension_loaded('newrelic')) {
            $this->loaded = true;
        }

        if ( !$ignore && !$this->loaded )
        {
            throw new \RuntimeException('NewRelic PHP Agent does not appear to be installed');
        }

    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     *
     * newrelic_set_appname (name [, license [, xmit]] )
     *
     * Sets the name of the application to name. The string uses the same format as newrelic.appname and 
	 * can set multiple application names by separating each with a semi-colon (;). 
	 * However, be aware of the restriction on the application name ordering as described for that setting..
     *
     * The first application name is the primary name, and up to two extra application names can be specified
     * (however the same application name can only ever be used once as a primary name). This function should be
     * called as early as possible, and will have no effect if called after the RUM footer has been sent. You may want
     * to consider setting the application name in a file loaded by PHP's auto_prepend_file INI setting. This function
     * returns true if it succeeded or false otherwise.
	 *
	 * If you use multiple licenses you can also specify a license key along with the application name. 
	 * An application can appear in more than one account and the license key controls which account you are changing the name in. 
	 * If you do not wish to change the license and wish to use the third variant, simply set the license key to the empty string 	 
	 *
     *
     * @param $name
     */
    public function setAppname($name)
    {
        return $this->_call('newrelic_set_appname', $name);
    }

    /**
     * newrelic_notice_error (message [, exception] )
     *
     * Report an error at this line of code, with a complete stack trace. The exception parameter must be a valid
     * PHP Exception class, and the stack frame recorded in that class will be the one reported, rather than the stack
     * at the time this function was called. When using this form, if the error message is empty, a standard message
     * in the same format as created by Exception::__toString() will be automatically generated.
     *
     * @param \Exception $e
     * @param null       $message
     */
    public function noticeException($message = null, $exception )
    {

        if ( $exception instanceof \Exception )
        {
            return $this->_call('newrelic_notice_error', $message, $exception);
        }
        else
        {
            return $this->_call('newrelic_notice_error', $message);
        }

    }

    /**
     * newrelic_notice_error (unused, message, unused, unused, unused)
     *
     * Report an error at this line of code, with a complete stack trace.
     *
     * Note: With the second form of the call, only the message is used.
     * This form of the call was designed to be a valid function passed to the PHP internal function set_error_handler().
     *
     * @param string $errnr The error code number
     * @param string $message The error message
     * @param string $funcname The name of the function
     * @param string $linenr The line number
     * @param string $errcontext The context of this error
     */
    public function noticeError($errnr, $message, $funcname ="", $linenr="", $errcontext="")
    {
        return $this->_call('newrelic_notice_error', $errnr, $message, $funcname, $linenr, $errcontext);
    }

    /**
     * Sets the name of the transaction to the specified string. This can be useful if you have implemented your own
     * dispatching scheme and wish to name transactions according to their purpose rather than their URL.
     *
     * Keep in mind that you want to make sure that you do not create too many unique transaction names. For example,
     * if you have /product/123 and /product/234, if you generate a separate transaction name for each, then New Relic
     * will store separate information for these two transaction names. This will make your graphs less useful, and may
     * run into limits we set on the number of unique transaction names per account. It also can slow down the
     * performance of your application. Instead, store the transaction as /product/*, or use something significant about
     * the code itself to name the transaction, such as /Product/view. The limit for the total number of transactions
     * should be less than 1000 unique transaction names -- exceeding that is not recommended.
     *
     * @param $name
     */
    public function nameTransaction($name)
    {
        return $this->_call('newrelic_name_transaction', $name);
    }

    /**
     *
     * newrelic_name_transaction (string)
     *
     * Stop recording the web transaction immediately. Usually used when a page is done with all computation and is
     * about to stream data (file download, audio or video streaming etc) and you don't want the time taken to stream
     * to be counted as part of the transaction. This is especially relevant when the time taken to complete the
     * operation is completely outside the bounds of your application. For example, a user on a very slow connection
     * may take a very long time to download even small files, and you wouldn't want that download time to skew the
     * real transaction time.
     */
    public function endOfTransaction()
    {
        return $this->_call('newrelic_end_of_transaction');
    }

    /**
     *
     * newrelic_end_transaction ( [ignore] )
     *
     * Note: this API call was introduced in version 3.0 of the agent.
     *
     * Despite being similar in name to newrelic_end_of_transaction above, this call serves a very different purpose.
     * newrelic_end_of_transaction simply marks the end time of the transaction but takes no other action.
     * The transaction is still only sent to the daemon when the PHP engine determines that the script is done
     * executing and is shutting down. This function on the other hand, causes the current transaction to end
     * immediately, and will ship all of the metrics gathered thus far to the daemon. In effect it simulates what would
     * happen then PHP terminates the current transaction. This is most commonly used in command line scripts that do
     * some form of job queue processing. You would use this call at the end of processing a single job task, and begin
     * a new transaction (see below) when a new task is pulled off the queue.
     *
     * Normally, when you end a transaction you want the metrics that have been gathered thus far to be recorded.
     * However, there are times when you may want to end a transaction without doing so.
     * In this case use the second form of the function and set ignore to true.
     *
     */
    public function endTransaction()
    {
        return $this->_call('newrelic_end_transaction');
    }

    /**
     *
     * newrelic_start_transaction (appname [, license] )
     *
     * Note: this API call was introduced in version 3.0 of the agent.
     *
     * If you have ended a transaction before your script terminates (perhaps due to it just having finished a task in
     * a job queue manager) and you want to start a new transaction, use this call. This will perform the same
     * operations that occur when the script was first started. Of the two arguments, only the transaction name is
     * mandatory. However, if you are processing tasks for multiple accounts you may also provide a license, in which
     * case that will be the license used, rather than any per-directory to global default one configured in INI files.
     *
     * @param      $name
     * @param null $license
     */
    public function startTransaction($name, $license = null)
    {
        return $this->_call('newrelic_start_transaction', $name, $license);
    }

    /**
     *
     * newrelic_ignore_transaction (  )
     *
     * Do not generate metrics for this transaction. This is useful when you have transactions that are particularly
     * slow for known reasons and you do not want them always being reported as the transaction trace or skewing your
     * site averages.
     */
    public function ignoreTransaction()
    {
        return $this->_call('newrelic_ignore_transaction');
    }

    /**
     *
     * newrelic_ignore_apdex (  )
     *
     * Do not generate metrics for this transaction. This is useful when you have transactions that are particularly
     * slow for known reasons and you do not want them always being reported as the transaction trace or skewing your
     * site averages.
     */
    public function ignoreApdex()
    {
        return $this->_call('newrelic_ignore_apdex');
    }

    /**
     *
     * newrelic_background_job ( [flag] )
     *
     * If no argument or true as an argument is given, mark the current transaction as a background job. If false is
     * passed as an argument, mark the transaction as a web transaction.
     *
     * @param bool $flag
     */
    public function backgroundJob($flag = true)
    {
        return $this->_call('newrelic_background_job', $flag);
    }

    /**
     *
     * newrelic_capture_params ( [enable] )
     *
     * If enable is omitted or set to on, enables the capturing of URL parameters for displaying in transaction traces.
     * In essence this overrides the newrelic.capture_params setting. In agents prior to 2.1.3 this was called
     * newrelic_enable_params() but that name is now deprecated.
     *
     * @param bool $flag
     */
    public function captureParams($flag = true)
    {
        return $this->_call('newrelic_capture_params', $flag);
    }

    /**
     *
     * newrelic_custom_metric (metric_name, value)
     *
     * Adds a custom metric with the specified name and value, which is of type double.
     * Values saved are assumed to be milliseconds, so "4" will be stored as ".004" in our system.
     * Your custom metrics can then be used in custom dashboards and custom views in the New Relic user interface.
     * Name your custom metrics with a Custom/ prefix (for example, Custom/MyMetric).
     * This will make them easily usable in custom dashboards
     *
     * Note: Avoid creating too many unique custom metric names.
     * New Relic limits the total number of custom metrics
     * you can use (not the total you can report for each of these custom metrics).
     * Exceeding more than 2000 unique custom metric names can cause automatic clamps
     * that will affect other data.
     *
     * @param $name
     * @param $value
     */
    public function customMetric($name, $value)
    {
        return $this->_call('newrelic_custom_metric', $name, $value);
    }

    /**
     *
     * newrelic_add_custom_parameter (key, value)
     *
     * Add a custom parameter to the current web transaction with the specified value. For example, you can add a
     * customer's full name from your customer database. This parameter is shown in any transaction trace that results
     * from this transaction.
     *
     * @param $key
     * @param $value
     */
    public function addCustomParameter($key, $value)
    {
        return $this->_call('newrelic_add_custom_parameter', $key, $value);
    }

    /**
     *
     * newrelic_add_custom_tracer (function_name)
     * newrelic_add_custom_tracer (classname::function_name)
     *
     * API equivalent of the newrelic.transaction_tracer.custom setting. It allows you
     * to add user defined functions or methods to the list to be instrumented.
     * Internal PHP functions cannot have custom tracing..
     *
     * @param $key
     * @param $value
     */
    public function addCustomTracer($function)
    {
       return $this->_call('newrelic_add_custom_tracer', $function);
    }


    /**
     *
     * newrelic_get_browser_timing_header ( [flag] )
     *
     * Returns the JavaScript string to inject as part of the header for browser timing (real user monitoring). If flag
     * is specified it must be a boolean, and if omitted, defaults to true. This indicates whether or not surrounding
     * script tags should be returned as part of the string.
     *
     * @param bool $flag
     */
    public function getBrowserTimingHeader($flag = true)
    {
        return $this->_call('newrelic_get_browser_timing_header', $flag);
    }

    /**
     *
     * newrelic_get_browser_timing_footer ([ flag] )
     *
     * Returns the JavaScript string to inject at the very end of the HTML output for browser timing
     * (real user monitoring). If flag is specified it must be a boolean, and if omitted, defaults to true.
     * This indicates whether or not surrounding script tags should be returned as part of the string.
     *
     * @param bool $flag
     */
    public function getBrowserTimingFooter($flag = true)
    {
        return $this->_call('newrelic_get_browser_timing_footer', $flag);
    }

    /**
     *
     * newrelic_disable_autorum (  )
     *
     * Prevents the output filter from attempting to insert RUM JavaScript for this current transaction.
     * Useful for AJAX calls, for example.
     */
    public function disableAutoRUM()
    {
        return $this->_call('newrelic_disable_autorum');
    }

    /**
     *
     * newrelic_set_user_attributes (user, account, product)
     *
     * Adds the three parameter strings to collected browser traces.
     * All three parameters are required, but may be empty strings.
     * For more information please see the section on browser traces..
     */
    public function setUserAttributes($user, $account, $product)
    {
        if (empty($user)) {
            $user = "";
        }
        if (empty($account)) {
            $account = "";
        }
        if (empty($product)) {
            $product = "";
        }
        return $this->_call('newrelic_set_user_attributes',$user, $account, $product);
    }


    /**
     * Guarding method to make sure extension is loaded and function exists in the actual client.
     * @param $new_relic_function name of the new relic function to call.
     *
     * @return response from native new relic method
     */
    private function _call($new_relic_function)
    {
        $params = func_get_args();
        array_shift($params);

        if ($this->loaded && function_exists($new_relic_function)) {
            return call_user_func_array($new_relic_function, $params);
        }
    }

}

