<?php

namespace Azay\Monolog\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;

/**
 * Formats incoming records into multiline text for content and extra entries
 *
 */
final class MultiLineFormatter implements FormatterInterface
{
    const PREFER_JSON_STYLE = 0;
    const PREFER_VAR_DUMP_STYLE = 1;
    const PREFER_PLAIN_TEXT_STYLE = 2;

    protected $space = ' ';
    protected $break = "\n";
    protected $dateFormat;
    protected $formatStyle;
    protected $lineBreaks;

    public function __construct( int $formatStyle = self::PREFER_JSON_STYLE, string $dateFormat = 'c', bool $lineBreaks = true )
    {
        $this->dateFormat = $dateFormat;
        $this->formatStyle = $formatStyle;
        $this->lineBreaks = $lineBreaks;
    }

    public function format( array $record ): string
    {
        $output = date( $this->dateFormat )
            . $this->space
            . '[' . Logger::getLevelName( $record[ 'level' ] ) . ']'
            . $this->space
            . $record[ 'message' ]
            . $this->break;

        if ( !empty( $record[ 'context' ] ) )
            $output .= $this->arrayConvert( $record[ 'context' ] );

        if ( !empty( $record[ 'extra' ] ) )
            $output .= $this->arrayConvert( $record[ 'extra' ] );

        if ( $this->lineBreaks )
            $output .= $this->break;

        return $output;
    }

    public function formatBatch( array $records ): string
    {
        $message = '';
        foreach ( $records as $record ) {
            $message .= $this->format( $record );
        }

        return $message;
    }

    private function arrayConvert( array $entries ): string
    {
        $text = '';
        foreach ( $entries as $key => $value ) {

            $text .= is_int( $key )
                ? $this->printable( $value ) . $this->break
                : $this->printable( $key ) . ': ' . $this->printable( $value ) . $this->break;
        }

        return $text . $this->break;
    }

    private function printable( $arg ): string
    {
        if ( is_scalar( $arg ) )
            return $arg;

        if ( empty( $arg ) )
            return '';

        switch ( $this->formatStyle ) {

            case self::PREFER_JSON_STYLE:
                $json = json_encode( $arg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT );
                return $json === false
                    ? print_r( $arg, true )
                    : $json;

            case self::PREFER_VAR_DUMP_STYLE:
                return print_r( $arg, true );

            default:
                if ( is_array( $arg ) )
                    return $this->arrayToString( $arg );
                else
                    return $arg;
        }
    }

    private function arrayToString( array $arr )
    {
        $result = '';
        $n = 0;
        foreach ( $arr as $value ) {
            if ( is_array( $value ) )
                $result .= ( $n++ === 0 ? '' : $this->space ) . '[' . $this->arrayToString( $value ) . ']';
            else
                $result .= ( $n++ === 0 ? '' : $this->space ) . $value;
        }
        return $result;
    }
}