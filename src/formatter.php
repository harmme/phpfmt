<?php

namespace Extern {
    require '../vendor/symfony/console/Formatter/OutputFormatterInterface.php';
    require '../vendor/symfony/console/Helper/HelperInterface.php';
    require '../vendor/symfony/console/Helper/Helper.php';
    require '../vendor/symfony/console/Formatter/OutputFormatterStyleStack.php';
    require '../vendor/symfony/console/Formatter/OutputFormatterStyleInterface.php';
    require '../vendor/symfony/console/Formatter/OutputFormatterStyle.php';
    require '../vendor/symfony/console/Formatter/OutputFormatter.php';
    require '../vendor/symfony/console/Output/OutputInterface.php';
    require '../vendor/symfony/console/Output/ConsoleOutputInterface.php';
    require '../vendor/symfony/console/Output/Output.php';
    require '../vendor/symfony/console/Output/StreamOutput.php';
    require '../vendor/symfony/console/Output/ConsoleOutput.php';
    require '../vendor/symfony/console/Helper/ProgressBar.php';
}

namespace phpformatter {
    class phpfmt {

        public static function run($opts, $file)
        {
            {
                $concurrent = function_exists('pcntl_fork');
                if ($concurrent) {
                    require '../csp.php';
                }
                require '../Core/Cacher.php';
                $enableCache = false;
                if (class_exists('SQLite3')) {
                    $enableCache = true;
                    require '../Core/Cache.php';
                } else {
                    require '../Core/Cache_dummy.php';
                }

                require '../version.php';
                require '../helpers.php';
                require '../selfupdate.php';

                require '../Core/constants.php';
                require '../Core/FormatterPass.php';
                require '../Additionals/AdditionalPass.php';
                require '../Core/BaseCodeFormatter.php';
                if ('1' === getenv('FMTDEBUG') || 'step' === getenv('FMTDEBUG')) {
                    require '../Core/CodeFormatter_debug.php';
                } elseif ('profile' === getenv('FMTDEBUG')) {
                    require '../Core/CodeFormatter_profile.php';
                } else {
                    require '../Core/CodeFormatter.php';
                }

                require '../Core/AddMissingCurlyBraces.php';
                require '../Core/AutoImport.php';
                require '../Core/ConstructorPass.php';
                require '../Core/EliminateDuplicatedEmptyLines.php';
                require '../Core/ExternalPass.php';
                require '../Core/ExtraCommaInArray.php';
                require '../Core/LeftAlignComment.php';
                require '../Core/MergeCurlyCloseAndDoWhile.php';
                require '../Core/MergeDoubleArrowAndArray.php';
                require '../Core/MergeParenCloseWithCurlyOpen.php';
                require '../Core/NormalizeIsNotEquals.php';
                require '../Core/NormalizeLnAndLtrimLines.php';
                require '../Core/Reindent.php';
                require '../Core/ReindentColonBlocks.php';
                require '../Core/ReindentComments.php';
                require '../Core/RemoveComments.php';
                require '../Core/ReindentEqual.php';
                require '../Core/ReindentObjOps.php';
                require '../Core/ResizeSpaces.php';
                require '../Core/RTrim.php';
                require '../Core/SettersAndGettersPass.php';
                require '../Core/SplitCurlyCloseAndTokens.php';
                require '../Core/StripExtraCommaInList.php';
                require '../Core/SurrogateToken.php';
                require '../Core/TwoCommandsInSameLine.php';

                require '../PSR/PSR1BOMMark.php';
                require '../PSR/PSR1ClassConstants.php';
                require '../PSR/PSR1ClassNames.php';
                require '../PSR/PSR1MethodNames.php';
                require '../PSR/PSR1OpenTags.php';
                require '../PSR/PSR2AlignObjOp.php';
                require '../PSR/PSR2CurlyOpenNextLine.php';
                require '../PSR/PSR2IndentWithSpace.php';
                require '../PSR/PSR2KeywordsLowerCase.php';
                require '../PSR/PSR2LnAfterNamespace.php';
                require '../PSR/PSR2ModifierVisibilityStaticOrder.php';
                require '../PSR/PSR2SingleEmptyLineAndStripClosingTag.php';
                require '../PSR/PsrDecorator.php';

                require '../Additionals/AddMissingParentheses.php';
                require '../Additionals/AliasToMaster.php';
                require '../Additionals/AlignConstVisibilityEquals.php';
                require '../Additionals/AlignDoubleArrow.php';
                require '../Additionals/AlignDoubleSlashComments.php';
                require '../Additionals/AlignEquals.php';
                require '../Additionals/AlignGroupDoubleArrow.php';
                require '../Additionals/AlignPHPCode.php';
                require '../Additionals/AlignTypehint.php';
                require '../Additionals/AllmanStyleBraces.php';
                require '../Additionals/AutoPreincrement.php';
                require '../Additionals/AutoSemicolon.php';
                require '../Additionals/CakePHPStyle.php';
                require '../Additionals/ClassToSelf.php';
                require '../Additionals/ClassToStatic.php';
                require '../Additionals/ConvertOpenTagWithEcho.php';
                require '../Additionals/DocBlockToComment.php';
                require '../Additionals/DoubleToSingleQuote.php';
                require '../Additionals/EchoToPrint.php';
                require '../Additionals/EncapsulateNamespaces.php';
                require '../Additionals/GeneratePHPDoc.php';
                require '../Additionals/IndentTernaryConditions.php';
                require '../Additionals/JoinToImplode.php';
                require '../Additionals/LeftWordWrap.php';
                require '../Additionals/LongArray.php';
                require '../Additionals/MergeElseIf.php';
                require '../Additionals/SplitElseIf.php';
                require '../Additionals/MergeNamespaceWithOpenTag.php';
                require '../Additionals/MildAutoPreincrement.php';
                require '../Additionals/NewLineBeforeReturn.php';
                require '../Additionals/NoSpaceAfterPHPDocBlocks.php';
                require '../Additionals/OrganizeClass.php';
                require '../Additionals/OrderAndRemoveUseClauses.php';
                require '../Additionals/OnlyOrderUseClauses.php';
                require '../Additionals/OrderMethod.php';
                require '../Additionals/OrderMethodAndVisibility.php';
                require '../Additionals/PHPDocTypesToFunctionTypehint.php';
                require '../Additionals/PrettyPrintDocBlocks.php';
                require '../Additionals/PSR2EmptyFunction.php';
                require '../Additionals/PSR2MultilineFunctionParams.php';
                require '../Additionals/ReindentAndAlignObjOps.php';
                require '../Additionals/ReindentSwitchBlocks.php';
                require '../Additionals/RemoveIncludeParentheses.php';
                require '../Additionals/RemoveSemicolonAfterCurly.php';
                require '../Additionals/RemoveUseLeadingSlash.php';
                require '../Additionals/ReplaceBooleanAndOr.php';
                require '../Additionals/ReplaceIsNull.php';
                require '../Additionals/RestoreComments.php';
                require '../Additionals/ReturnNull.php';
                require '../Additionals/ShortArray.php';
                require '../Additionals/SmartLnAfterCurlyOpen.php';
                require '../Additionals/SortUseNameSpace.php';
                require '../Additionals/SpaceAroundControlStructures.php';
                require '../Additionals/SpaceAroundExclamationMark.php';
                require '../Additionals/SpaceBetweenMethods.php';
                require '../Additionals/StrictBehavior.php';
                require '../Additionals/StrictComparison.php';
                require '../Additionals/StripExtraCommaInArray.php';
                require '../Additionals/StripNewlineAfterClassOpen.php';
                require '../Additionals/StripNewlineAfterCurlyOpen.php';
                require '../Additionals/StripNewlineWithinClassBody.php';
                require '../Additionals/StripSpaces.php';
                require '../Additionals/StripSpaceWithinControlStructures.php';
                require '../Additionals/TightConcat.php';
                require '../Additionals/TrimSpaceBeforeSemicolon.php';
                require '../Additionals/UpgradeToPreg.php';
                require '../Additionals/WordWrap.php';
                require '../Additionals/WrongConstructorName.php';
                require '../Additionals/YodaComparisons.php';

                $fmt = new CodeFormatter();
                if (isset($opts['setters_and_getters'])) {
                    $fmt->enablePass('SettersAndGettersPass', $opts['setters_and_getters']);
                }

                if (isset($opts['constructor'])) {
                    $fmt->enablePass('ConstructorPass', $opts['constructor']);
                }

                if (isset($opts['oracleDB'])) {
                    if ('scan' == $opts['oracleDB']) {
                        $oracle = getcwd() . DIRECTORY_SEPARATOR . 'oracle.sqlite';
                        $lastoracle = '';
                        while (!is_file($oracle) && $lastoracle != $oracle) {
                            $lastoracle = $oracle;
                            $oracle = dirname(dirname($oracle)) . DIRECTORY_SEPARATOR . 'oracle.sqlite';
                        }
                        $opts['oracleDB'] = $oracle;
                        fwrite(STDERR, PHP_EOL);
                    }

                    if (file_exists($opts['oracleDB']) && is_file($opts['oracleDB'])) {
                        $fmt->enablePass('AutoImportPass', $opts['oracleDB']);
                    }
                }

                if (isset($opts['smart_linebreak_after_curly'])) {
                    $fmt->enablePass('SmartLnAfterCurlyOpen');
                }

                if (isset($opts['remove_comments'])) {
                    $fmt->enablePass('RemoveComments');
                }

                if (isset($opts['yoda'])) {
                    $fmt->enablePass('YodaComparisons');
                }

                if (isset($opts['enable_auto_align'])) {
                    $fmt->enablePass('AlignEquals');
                    $fmt->enablePass('AlignDoubleArrow');
                }

                if (isset($opts['psr'])) {
                    PsrDecorator::decorate($fmt);
                }

                if (isset($opts['psr1'])) {
                    PsrDecorator::PSR1($fmt);
                }

                if (isset($opts['psr1-naming'])) {
                    PsrDecorator::PSR1Naming($fmt);
                }

                if (isset($opts['psr2'])) {
                    PsrDecorator::PSR2($fmt);
                }

                if (isset($opts['indent_with_space'])) {
                    $fmt->enablePass('PSR2IndentWithSpace', $opts['indent_with_space']);
                }

                if ((isset($opts['psr1']) || isset($opts['psr2']) || isset($opts['psr'])) && isset($opts['enable_auto_align'])) {
                    $fmt->enablePass('PSR2AlignObjOp');
                }

                if (isset($opts['visibility_order'])) {
                    $fmt->enablePass('PSR2ModifierVisibilityStaticOrder');
                }

                if (isset($opts['passes'])) {
                    $optPasses = array_map(function ($v) {
                        return trim($v);
                    }, explode(',', $opts['passes']));
                    foreach ($optPasses as $optPass) {
                        $fmt->enablePass($optPass);
                    }
                }

                if (isset($opts['cakephp'])) {
                    $fmt->enablePass('CakePHPStyle');
                }

                if (isset($opts['php2go'])) {
                    Php2GoDecorator::decorate($fmt);
                }

                if (isset($opts['exclude'])) {
                    $passesNames = explode(',', $opts['exclude']);
                    foreach ($passesNames as $passName) {
                        $fmt->disablePass(trim($passName));
                    }
                }

                if (isset($opts['v'])) {
                    fwrite(STDERR, 'Used passes: ' . implode(', ', $fmt->getPassesNames()) . PHP_EOL);
                }

                if (isset($opts['o'])) {
                    if(!isset($file)){
                        $file = '-';
                    }
                    if ('-' == $opts['o'] && '-' == $file) { /** $file es el nombre del archivo **/
                        echo $fmt->formatCode(file_get_contents('php://stdin'));
                    }
                    if (!is_file($file)) { /** $file es el nombre del archivo **/
                        fwrite(STDERR, 'File not found: ' . $file . PHP_EOL); /** $file es el nombre del archivo **/
                    }
                    if ('-' == $opts['o']) {
                        echo $fmt->formatCode(file_get_contents($file));
                    }
                    file_put_contents($opts['o'], $fmt->formatCode(file_get_contents($file)));
                } elseif (isset($file)) {
                    if ('-' == $file) {
                        echo $fmt->formatCode(file_get_contents('php://stdin'));
                    }
                }


            }
        }
    }
}