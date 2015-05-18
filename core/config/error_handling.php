<?php
// clear current
error_reporting(0);

// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars){

    ob_start();
    $err_level =  2 << ERROR_LEVEL - 1;
    
    if($errno <= $err_level){
        
        $err =  'Into '.__FUNCTION__.'() at line '.__LINE__.
          "\n\n---ERRNO---\n". print_r( $errno, true).
          "\n\n---ERRSTR---\n". print_r( $errmsg, true).
          "\n\n---ERRFILE---\n". print_r( $filename, true).
          "\n\n---ERRLINE---\n". print_r( $linenum, true).
          "\n\n---ERRCONTEXT---\n".print_r( $vars, true);
        
        $styles = array(
            'common'=>array(
                'padding-left'=>    '5px',
                'margin-left'=>     '5px',
                'margin-bottom'=>   '1px',
                'color'=>           'black',
                'background-color'=>'white',
                'position'=>        'relative',
                'overflow'=>'scroll'
            ),
            'green' => array(
                'border-top'=>      '5px solid green',
                'border-left'=>     '5px solid green',
                'border-bottom'=>   '1px dotted green'
            ),
            'red' => array(
                'border-bottom'=>       '5px solid red',
                'border-left'=>     '5px solid red',
                'border-top'=>  '1px dotted red',
                'margin-bottom'=>   '50px'
            ),
        );
        $green_style = '';
        foreach(array_merge($styles['common'], $styles['green']) as $sel =>$val)$green_style.=$sel.':'.$val.';';

        $red_style = '';
        foreach(array_merge($styles['common'], $styles['red']) as $sel =>$val)$red_style.=$sel.':'.$val.';';
        
        echo '<div style="'.$green_style.'"><pre>'.$err.'</pre></div>';
        
        if(STACKTRACE_ENABLED){
            echo '<div style="'.$red_style.'">STACKTRACE:<pre>'.
                "\n\nBacktrace of errorHandler()\n".debug_print_backtrace()
                .'</pre></div>';
        }        
    }
    
    DSMVC::$errors = ob_get_clean();

    //if(DSMVC::$errors) error_log(DSMVC::$errors, 3, "../php_errors.log");
    if (DSMVC::$errors) { 
        echo DSMVC::$errors;
    }

}

$old_error_handler = set_error_handler("userErrorHandler");
