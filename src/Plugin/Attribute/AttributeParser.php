<?php

/**
 * AnnotationReader.php
 *
 * Jaxon annotation reader.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Attribute;

use ReflectionClass;

use function array_filter;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function preg_replace;
use function substr;
use function strrpos;
use function strtolower;
use function token_get_all;
use function trim;

class AttributeParser
{
    /**
     * @param string $sCacheDir
     */
    public function __construct(private string $sCacheDir)
    {}

    /**
     * Slugify a string
     *
     * @param string $str
     *
     * @return string
     */
    private function slugify(string $str): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $str), '-'));
    }

    /**
     * @param string $sFileName
     *
     * @return array
     */
    private function getTokens(string $sFileName): array
    {
        $aTokens = token_get_all(file_get_contents($sFileName));

        $aHeaderTokens = [];
        $aUseTokens = [];
        foreach($aTokens as $xToken)
        {
            if(!is_array($xToken))
            {
                continue;
            }

            switch($xToken[0])
            {
            case T_USE:
                $aUseTokens[] = $xToken;
                break;
            case T_CLASS:
                // Stop on the "class" token.
                return [$aUseTokens, $aHeaderTokens];
            default:
                $aHeaderTokens[] = $xToken;
            }
        }
        // No "class" token in the file. Do nothing.
        return [null, null];
    }

    /**
     * Get the other tokens related to a "use" statement.
     *
     * @param array $aUseToken
     * @param array $aHeaderTokens
     *
     * @return array
     */
    private function getUseStatement(array $aUseToken, array $aHeaderTokens): array
    {
        // The other tokens related to the "use statement" are supposed to be on
        // the same line in the source file.
        // The token type of the imported value is T_NAME_QUALIFIED.
        // If an alias is defined with the "as" keyword, its token type is T_STRING.
        // These two tokens are returned in an array with their respective types as key.
        /** @var int */
        $nLineNumber = $aUseToken[2];
        $aUseTokens = array_filter($aHeaderTokens, fn(array $aToken) =>
            $aToken[2] === $nLineNumber && ($aToken[0] === T_NAME_QUALIFIED || $aToken[0] === T_STRING));

        // Key the tokens values by type
        $aUseStatement = [];
        foreach($aUseTokens as $aToken)
        {
            $aUseStatement[$aToken[0]] = $aToken[1];
        }
        return $aUseStatement;
    }

    /**
     * Get the name of the item defined by a "use" statement
     *
     * @param array $aUseStatement
     *
     * @return string
     */
    private function getStatementKey(array $aUseStatement): string
    {
        if(isset($aUseStatement[T_STRING]))
        {
            // The statement is "use <type> as <name>;". <name> is the key.
            return $aUseStatement[T_STRING];
        }

        // The statement is "use <type>;". <type> is parsed to get the key.
        $sValue = $aUseStatement[T_NAME_QUALIFIED];
        $nSeparatorPosition = strrpos($sValue, '\\');
        return $nSeparatorPosition === false ? $sValue : substr($sValue, $nSeparatorPosition + 1);
    }

    /**
     * @param ReflectionClass $xReflectionClass
     *
     * @return array
     */
    public function readImportedTypes(ReflectionClass $xReflectionClass): array
    {
        $sFileName = $xReflectionClass->getFileName();
        [$aUseTokens, $aHeaderTokens] = $this->getTokens($sFileName);
        if(!$aUseTokens)
        {
            return [];
        }

        $aImportedTypes = [];
        foreach($aUseTokens as $aUseToken)
        {
            $aUseStatement = $this->getUseStatement($aUseToken, $aHeaderTokens);
            $aImportedTypes[$this->getStatementKey($aUseStatement)] = $aUseStatement[T_NAME_QUALIFIED];
        }

        // Save the types in the cache file
        // $sFilePath = $this->sCacheDir . '/' . $this->slugify($sFileName) . '.json';
        // file_put_contents($sFilePath, json_encode($aImportedTypes));

        return $aImportedTypes;
    }
}
