<?php
namespace manguto\cms7\libraries;

/**
 * Regular Expressions, commonly known as "regex" or "RegExp", are a specially formatted text strings used to find patterns in text.
 * Regular expressions are one of the most powerful tools available today for effective and efficient text processing and manipulations.
 * For example, it can be used to verify whether the format of data i.e. name, email, phone number, etc. entered by the user was correct
 * or not, find or replace matching string within text content, and so on.
 * 
 * @see https://www.tutorialrepublic.com/php-tutorial/php-regular-expressions.php
 * @see https://www.phpliveregex.com/p/vSj#tab-preg-match-all [abc] Matches any one of the characters a, b, or c.
 * @author Marcos     
 *     
 */
class RegexPatternTool
{

    public function __construct()
    {}
}

/**
 * =======================================================================================-
 * =================================================================================- AJUDA
 * =======================================================================================-
 * [^abc] - Matches any one character other than a, b, or c.
 * [a-z] - Matches any one character from lowercase a to lowercase z.
 * [A-Z] - Matches any one character from uppercase a to uppercase z.
 * [a-Z] - Matches any one character from lowercase a to uppercase Z.
 * [0-9] - Matches a single digit between 0 and 9.
 * [a-z0-9] - Matches a single character between a and z or between 0 and 9.
 * =======================================================================================-
 * . - Matches any single character except newline \n.
 * \d matches any digit character. Same as [0-9]
 * \D - Matches any non-digit character. Same as [^0-9]
 * \s - Matches any whitespace character (space, tab, newline or carriage return character). Same as [ \t\n\r]
 * \S - Matches any non-whitespace character. Same as [^ \t\n\r]
 * \w - Matches any word character (definned as a to z, A to Z,0 to 9, and the underscore). Same as [a-zA-Z_0-9]
 * \W - Matches any non-word character. Same as [^a-zA-Z_0-9]
 * =======================================================================================-
 * p+ - Matches one or more occurrences of the letter p.
 * p* - Matches zero or more occurrences of the letter p.
 * p? - Matches zero or one occurrences of the letter p.
 * p{2} - Matches exactly two occurrences of the letter p.
 * p{2,3} - Matches at least two occurrences of the letter p, but not more than three occurrences of the letter p.
 * p{2,} - Matches two or more occurrences of the letter p.
 * p{,3} - Matches at most three occurrences of the letter p
 * ^p Matches the letter p at the beginning of a line.
 * p$ Matches the letter p at the end of a line.
 * =======================================================================================-
 * Modifier What it Does
 * i Makes the match case-insensitive manner.
 * m Changes the behavior of ^ and $ to match against a newline boundary (i.e. start or end of each line within a multiline string), instead of a string boundary.
 * g Perform a global match i.e. finds all occurrences.
 * o Evaluates the expression only once.
 * s Changes the behavior of . (dot) to match all characters, including newlines.
 * x Allows you to use whitespace and comments within a regular expression for clarity.
 * =======================================================================================-
 * A word boundary character ( \b) helps you search for the words that
 * begins and/or ends with a pattern. For example, the regexp /\bcar/
 * matches the words beginning with the pattern car, and would match
 * cart, carrot, or cartoon, but would not match oscar.
 * =======================================================================================-
 */

?>