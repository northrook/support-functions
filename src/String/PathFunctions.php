<?php

namespace Northrook\src\String;

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Northrook\src\File;
use Northrook\src\Str;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * @internal
 * @author  Martin Nielsen <mn@northrook.com>
 */
trait PathFunctions
{

    #[Deprecated( 'Use ' . Str::class . '::getPath instead.' )]
    public static function filepath( string $path, ?string $fullPath = null ) : string {
        Log::Notice(
            'Using deprecated function {old}. Use {new} instead.',
            [
                'old' => Str::class . '::filepath',
                'new' => Str::class . '::getPath',
            ],
        );
        return Str::getPath( $path );
    }

    #[Deprecated]
    public static function parameterDirname(
        string  $path = '%kernel.project_dir%',
        #[ExpectedValues( [ 'log', 'error', 'exception' ] )]
        ?string $onInvalidPath = 'exception',
    ) : ?string {

        trigger_deprecation(
            'northrook/string',
            '1.0.0',
            'The method "%s" is deprecated. Use Symfony/Finder instead.',
            __METHOD__,
        );

        if ( false === str_starts_with( $path, '../' ) ) {
            return static::normalizePath( $path );
        }

        $level = substr_count( $path, '../', 0, strrpos( $path, '../' ) + 3 );
        $root  = dirname( debug_backtrace()[ 0 ][ 'file' ], $level ?: 1 );
        $path  = $root . '/' . substr( $path, strrpos( $path, '../' ) + 3 );

        $path = static::normalizePath( $path );

        if ( file_exists( $path ) ) {
            return $path;
        }

        match ( $onInvalidPath ) {
            'exception' => throw new FileNotFoundException( $path ),
            'error'     => trigger_error( "File \"$path\" does not exist.", E_USER_ERROR ),
            'log'       => Log::Error(
                message : 'File {path} does not exist.',
                context : [ 'path' => $path, 'file' => debug_backtrace()[ 0 ][ 'file' ] ],
            ),
            default     => null,
        };

        return $path;
    }

    public static function getPath( string $path, bool $create = false, ?string $fallback = null ) : ?string {
        if ( $create ) {
            return File::mkdir( $path );
        }

        $path = Str::normalizePath( string : $path );

        return File::exists( $path ) ? $path : $fallback;
    }

    public static function getDirectoryPath( string $path, bool $create = false ) : string {
        $path = substr( $path, 0, (int) strrpos( Str::getPath( $path, $create ), DIRECTORY_SEPARATOR ) );

        return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
    }

    public static function isPathTraversable( string $path ) : bool {
        return Str::contains( $path, [ ".", "../", ".." . DIRECTORY_SEPARATOR ] );
    }


    /**
     * Determine if a given value is a valid URL.
     *
     *  - This function is `Memoized`.
     *
     * @param mixed  $value
     * @param array  $protocols
     *
     * @return bool
     */
    public static function isUrl( mixed $value, array $protocols = [] ) : bool {

        if ( !is_string( $value ) ) {
            return false;
        }

        // Function from Laravel Illuminate\Support\Str
        $isUrl = static function ( mixed $value, array $protocols = [] ) : bool {

            /** @noinspection SpellCheckingInspection */
            $protocolList = empty( $protocols )
                ? 'aaa|aaas|about|acap|acct|acd|acr|adiumxtra|adt|afp|afs|aim|amss|android|appdata|apt|ark|attachment|aw|barion|beshare|bitcoin|bitcoincash|blob|bolo|browserext|calculator|callto|cap|cast|casts|chrome|chrome-extension|cid|coap|coap\+tcp|coap\+ws|coaps|coaps\+tcp|coaps\+ws|com-eventbrite-attendee|content|conti|crid|cvs|dab|data|dav|diaspora|dict|did|dis|dlna-playcontainer|dlna-playsingle|dns|dntp|dpp|drm|drop|dtn|dvb|ed2k|elsi|example|facetime|fax|feed|feedready|file|filesystem|finger|first-run-pen-experience|fish|fm|ftp|fuchsia-pkg|geo|gg|git|gizmoproject|go|gopher|graph|gtalk|h323|ham|hcap|hcp|http|https|hxxp|hxxps|hydrazone|iax|icap|icon|im|imap|info|iotdisco|ipn|ipp|ipps|irc|irc6|ircs|iris|iris\.beep|iris\.lwz|iris\.xpc|iris\.xpcs|isostore|itms|jabber|jar|jms|keyparc|lastfm|ldap|ldaps|leaptofrogans|lorawan|lvlt|magnet|mailserver|mailto|maps|market|message|mid|mms|modem|mongodb|moz|ms-access|ms-browser-extension|ms-calculator|ms-drive-to|ms-enrollment|ms-excel|ms-eyecontrolspeech|ms-gamebarservices|ms-gamingoverlay|ms-getoffice|ms-help|ms-infopath|ms-inputapp|ms-lockscreencomponent-config|ms-media-stream-id|ms-mixedrealitycapture|ms-mobileplans|ms-officeapp|ms-people|ms-project|ms-powerpoint|ms-publisher|ms-restoretabcompanion|ms-screenclip|ms-screensketch|ms-search|ms-search-repair|ms-secondary-screen-controller|ms-secondary-screen-setup|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-connectabledevices|ms-settings-displays-topology|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|ms-spd|ms-sttoverlay|ms-transit-to|ms-useractivityset|ms-virtualtouchpad|ms-visio|ms-walk-to|ms-whiteboard|ms-whiteboard-cmd|ms-word|msnim|msrp|msrps|mss|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|ocf|oid|onenote|onenote-cmd|opaquelocktoken|openpgp4fpr|pack|palm|paparazzi|payto|pkcs11|platform|pop|pres|prospero|proxy|pwid|psyc|pttp|qb|query|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|s3|secondlife|service|session|sftp|sgn|shttp|sieve|simpleledger|sip|sips|skype|smb|sms|smtp|snews|snmp|soap\.beep|soap\.beeps|soldat|spiffe|spotify|ssh|steam|stun|stuns|submit|svn|tag|teamspeak|tel|teliaeid|telnet|tftp|tg|things|thismessage|tip|tn3270|tool|ts3server|turn|turns|tv|udp|unreal|urn|ut2004|v-event|vemmi|ventrilo|videotex|vnc|view-source|wais|webcal|wpid|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc\.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s'
                : implode( '|', $protocols );

            // Regex Pattern from Symfony\Component\Validator\Constraints\UrlValidator.
            // (c) Fabien Potencier <fabien@symfony.com> http://symfony.com
            $pattern = '~^
            (LARAVEL_PROTOCOLS)://                                 # protocol
            (((?:[\_\.\pL\pN-]|%[0-9A-Fa-f]{2})+:)?((?:[\_\.\pL\pN-]|%[0-9A-Fa-f]{2})+)@)?  # basic auth
            (
                ([\pL\pN\pS\-\_\.])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                                 # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                    # an IP address
                    |                                                 # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # an IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*          # a path
            (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?   # a query (optional)
            (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?       # a fragment (optional)
            $~ixu';

            return preg_match( str_replace( 'LARAVEL_PROTOCOLS', $protocolList, $pattern ), $value ) > 0;
        };

        return Cached( $isUrl, [ $value, $protocols ] );
        // return static::memoize( $isUrl, $value, $protocols );
    }

    /**
     *  Normalise a `string`, assuming it is a `path`.
     *
     *  - Removes repeated slashes.
     *  - Normalises slashes to system separator.
     *  - Prevents backtracking.
     *  - No validation is performed.
     *  - This function is `Memoized`.
     *
     * Options:
     *  - Set `$allowFilePath` to `false` will throw an exception if the path has a file extension.
     *  - Set `$trailingSlash` to `false` will remove the trailing slash for directories.
     *
     *
     * @param ?string  $string
     * @param bool     $allowFilePath
     * @param bool     $trailingSlash
     *
     * @return ?string
     */
    public static function normalizePath(
        ?string $string,
        bool    $allowFilePath = true,
    ) : ?string {

        if ( !$string ) {
            return null;
        }

        $normalizePath = static function ( $string, $allowFilePath ) {

            $string = str_replace( "\\", "/", $string );

            if ( str_contains( $string, '/' ) ) {

                $path = [];

                foreach ( array_filter( explode( '/', $string ) ) as $part ) {
                    if ( $part === '..' && $path && end( $path ) !== '..' ) {
                        array_pop( $path );
                    }
                    elseif ( $part !== '.' ) {
                        $path[] = trim( $part );
                    }
                }

                $path = implode( DIRECTORY_SEPARATOR, $path );
            }
            else {
                $path = $string;
            }

            $extension = pathinfo( $path, PATHINFO_EXTENSION );

            if ( $extension && !$allowFilePath ) {
                throw new \InvalidArgumentException(
                    "Invalid path: $path.\n\nFile path not allowed.\n\nDirectory path required.",
                );
            }

            return realpath( $path ) ?: $path;
        };

        return Cached( $normalizePath, [ $string, $allowFilePath ] );
        // return static::memoize( $normalizePath, $string, $allowFilePath );
    }

    /**
     * @param ?string  $string
     * @param bool     $trailingSlash
     *
     * @return ?string
     *
     * @link https://github.com/glenscott/url-normalizer/blob/master/src/URL/Normalizer.php Good starting point
     */
    public static function normalizeURL(
        ?string $string,
        bool    $trailingSlash = true,
    ) : ?string {

        if ( !$string ) {
            return null;
        }

        return $trailingSlash ? rtrim( $string, '/' ) : $string;

        // $url = new UrlNormalizer(
        //     url : $string,
        // );

        // $normalizeURL = static function ( $string, $trailingSlash ) {
        //
        //     [ $url, $query ] = explode( '?', $string, 2 );
        //
        // };

        // return static::memoize( $normalizeURL, $string, $trailingSlash );
    }

    public static function href( string $string ) : string {
        $string = strtolower( trim( $string ) );

        if ( filter_var( $string, FILTER_VALIDATE_EMAIL ) ) {
            $string = \Northrook\src\Str::start( $string, 'mailto:' );
        }

        return $string;
    }

    #[Deprecated( 'Use Path' )]
    public static function url( ?string $string, bool $absolute = false, bool $trailing = false ) : ?string {

        $url = filter_var( $string, FILTER_SANITIZE_URL );

        $url = trim( $url, '/' );

        if ( $trailing ) {
            $url = rtrim( $url, '/' );
        }

        if ( !$absolute ) {
            $url = '/' . $url;
        }
        else {
            $url = $_SERVER[ 'SERVER_NAME' ] . '/' . $url;
        }

        return $url;
    }


}