<?php

namespace Northrook\Support\Config;

use Northrook\Core\Trait\StaticClass;

final class Stopwords
{
    use StaticClass;

    private static array $list = [
        'en' => [
            "i", "me", "my", "myself", "we", "our", "ours", "ourselves", "you", "your", "yours", "yourself",
            "yourselves", "he", "him", "his", "himself", "she", "her", "hers", "herself", "it", "its", "itself", "they",
            "them", "their", "theirs", "themselves", "what", "which", "who", "whom", "this", "that", "these", "those",
            "am", "is", "are", "was", "were", "be", "been", "being", "have", "has", "had", "having", "do", "does",
            "did", "doing", "a", "an", "the", "and", "but", "if", "or", "because", "as", "until", "while", "of", "at",
            "by", "for", "with", "about", "against", "between", "into", "through", "during", "before", "after", "above",
            "below", "to", "from", "up", "down", "in", "out", "on", "off", "over", "under", "again", "further", "then",
            "once", "here", "there", "when", "where", "why", "how", "all", "any", "both", "each", "few", "more", "most",
            "other", "some", "such", "no", "nor", "not", "only", "own", "same", "so", "than", "too", "very", "s", "t",
            "can", "will", "just", "don", "should", "now",
        ],
        'da' => [

        ],
    ];

    public static function add( string $word, string $group = 'en' ) : void {
        Stopwords::$list[ $group ][] = $word;
    }

    // TODO Add Debug::log() with a group is empty or missing
    public static function get( string $group = 'en' ) : array {
        return Stopwords::$list[ $group ] ?? [];
    }

    public static function remove( string $word, string $group = 'en' ) : void {
        unset( Stopwords::$list[ $group ][ $word ] );
    }

    public static function has( string $word, string $group = 'en' ) : bool {
        return isset( Stopwords::$list[ $group ][ $word ] );
    }

}