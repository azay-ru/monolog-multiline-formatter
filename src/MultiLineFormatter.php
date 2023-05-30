<?php

namespace Azay\Monolog;

use DateTimeImmutable;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;

/**
 * Formats incoming records into multiline text for content and extra entries
 *
 */
final class MultiLineFormatter implements FormatterInterface
{
    const DEFAULT_STYLE = 0;
    const VAR_DUMP_STYLE = 1;
    const JSON_STYLE = 2;

    private $space = ' ';
    private $break = "\n";
    private $dateFormat;
    private $formatStyle;
    private $lineBreaks;

    public function __construct( string $dateFormat = 'Y-m-d H:i:s', int $formatStyle = self::JSON_STYLE, bool $lineBreaks = true )
    {
        $this->dateFormat = $dateFormat;
        $this->formatStyle = $formatStyle;
        $this->lineBreaks = $lineBreaks;
    }

    public function format( array $record ): string
    {
        $output =
            $record[ 'datetime' ]->format( $this->dateFormat )
            . $this->space
            . ( empty( $record[ 'channel' ] ) ? '' : ( $record[ 'channel' ] . $this->space ) )
            . '[' . Logger::getLevelName( $record[ 'level' ] ) . ']'
            . $this->space
            . $record[ 'message' ]
            . $this->break;

        if ( !empty( $record[ 'context' ] ) )
            foreach ( $record[ 'context' ] as $key => $value ) {
                $output .= empty( $key )
                    ? $this->printable( $value )
                    : [ $key, $this->printable( $value ) ];
            }

        if ( !empty( $record[ 'extra' ] ) )
            $output .= $this->printable( $record[ 'extra' ] );

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

    private function printable( $arg )
    {
        if ( empty( $arg ) )
            return '' . $this->break;

        if ( is_bool( $arg ) )
            return ( $arg ? 'True' : 'False' ) . $this->break;

        if ( !is_array( $arg ) && !is_object( $arg ) )
            return $arg . $this->break;


        switch ( $this->formatStyle ) {

            case self::JSON_STYLE:
                $json = json_encode( $arg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT );
                if ( $json === false )
                    return $this->break;
                else
                    return $json . $this->break;

            case self::VAR_DUMP_STYLE:
                return print_r( $arg, true ) . $this->break;

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