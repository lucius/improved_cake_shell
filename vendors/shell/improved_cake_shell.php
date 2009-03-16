<?php

/*
 * @author: Marcus Vinicius - Lucius
 */
class ImprovedCakeShell extends Shell {

    var $_styles = array(
        'FAIL' => array(
            'bg' => 'red',
            'fg' => 'white',
            'b' => true
        ),
        'SUCCESS' => array(
            'bg' => 'green',
            'fg' => 'white',
            'b' => true
        )
    );

    var $_oldStyle = null;

    /* Formatting Options
     *
     * [b][/b] => bold
     * [u][/u] => underscore
     * [s][/s] => blink
     * [r][/r] => reverse colors
     * [c][/c] => conceal
     */
    var $_options = array(
        'b' => 1,
        'u' => 4,
        's' => 5,
        'r' => 7,
        'c' => 8
    );
   
    /* Foreground Colors
     *
     * [fg='colorname'][/fg] => Foreground Color
     */
    var $_foreground = array(
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'white' => 37
    );

    /* Background Colors
     *
     * [bg='colorname'][/bg] => Background Color
     */
    var $_background = array(
        'black' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 43,
        'blue' => 44,
        'magenta' => 45,
        'cyan' => 46,
        'white' => 47
    );

    function __construct( )
    {
        $args = func_get_args();
        parent::__construct($args[0]);
    }

    function formattedOut($text, $carriageReturn = true, $returnString = false)
    {
        if ( $this->_supportsColors() )
        {
            if(!$returnString)
            {
                $this->out($text, $carriageReturn);
            }
            else
            {
                return $text;
            }
        }

        $activeOptions = array(
            'b' => false,
            'u' => false,
            's' => false,
            'r' => false,
            'c' => false,
            'bg'=> false,
            'fg' => false
        );
        $newText = '';

        $found = array();
        preg_match_all('/\[([^]]+)\]/', $text, $found);
        foreach ( $found[1] as $key => $tag )
        {
            if( $tag[0] != '/' )
            {
                if ( $this->_enableOption($activeOptions, $tag) )
                {
                    $text = str_ireplace( $found[0][$key], "\033[0m"."\033[".$this->_compileOptions($activeOptions).'m', $text );
                }
                else
                {
                    $text = str_ireplace( $found[0][$key], "\033[".$this->_compileOptions($activeOptions).'m', $text );
                }
            }
            else
            {
                $this->_disableOption( $activeOptions, $found[1][$key] );
                $text = str_ireplace( $found[0][$key], "\033[0m"."\033[".$this->_compileOptions($activeOptions).'m', $text );
            }    
        }

        if ( $returnString )
        {
            return $text."\033[0m";
        }

        $this->out($text."\033[0m", $carriageReturn);
    }

    function _supportsColors( )
    {
        return DS != '\\' && function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

    function _compileOptions( $activeOptions )
    {
        $codes = array();
        if ( !empty($activeOptions['fg']) )
        {
            $codes[] = $this->_foreground[$activeOptions['fg']];
        }

        if ( !empty($activeOptions['bg']) )
        {
            $codes[] = $this->_background[$activeOptions['bg']];
        }

        foreach ( $this->_options as $key => $value )
        {
            if ( !empty($activeOptions[$key]) )
            {
                $codes[] = $value;
            }
        }

        $compiledOptions = implode(';', $codes);
        return $compiledOptions;
    }

    function _enableOption( &$activeOptions, $found )
    {
        $found = str_replace( ' ', '', $found );
        $modified = false;

        if ( array_key_exists($found, $this->_styles) )
        {
            $modified = true;
            $this->_oldStyle = $activeOptions;
            $activeOptions = $this->_styles[$found];
        }
        else
        {
            if( strlen($found) > 1 )
            {
                list($key, $value) = explode( '=', $found );
            }
            else
            {
                $key = $found;
                $value = true;
            }

            if ( $activeOptions[$key] != $value )
            {
                $activeOptions[$key] = $value;
                $modified = true;
            }
        }
        return $modified;
    }

    function _disableOption( &$activeOptions, $found )
    {
        $found = str_replace( '/', '', $found );
        
        if ( array_key_exists($found, $this->_styles) )
        {
            if ( !is_null($this->_oldStyle) )
            {
                $modified = true;
                $activeOptions = $this->_oldStyle;
                $this->_oldStyle = null; 
            }    
        }
        else
        {
            $activeOptions[$found] = false;
        }
        
        return true;
    }

    function addStyles( $newStyle )
    {
        $this->_styles = array_merge( $this->_styles, $newStyle );
    }
}
?>
