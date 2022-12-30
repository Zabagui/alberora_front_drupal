<?php

/**
 * @file
 * Contains \Drupal\alberora\Database.
 */

namespace Drupal\alberora\Database;

use Drupal\Core\Database\Database;
use Drupal\Component\Render\FormattableMarkup;

use Drupal\alberora\Charts\StatCharts;
use Drupal\alberora\Database\StatDatabaseQueries;
use Drupal\alberora\Log\StatLog;

class StatDatabase extends StatDatabaseQueries
{
    /**
     * @return array
     */
    private static function prepareChartPie(array $result): array
    {
        $columns = "";
        $colors = "";
        $charType = "donut";

        foreach ($result as $record) {
            $columns .= '["' . $record->TYPE . '", ' . $record->NB . "],";
            $colors .= '"' . $record->TYPE . '": "' . $record->COLOR . '",';
        }

        $chart = [];
        $chart[] = $columns;
        $chart[] = $colors;
        return $chart;
    }

    /**
     * @return array
     */
    private static function prepareChartLine(
        array $result,
        array $fieldsresult,
              $dbDateFormat
    ): array
    {
        $columns = "";
        $colors = "";
        $charType = "spline";
        $xName = "Date";

        $x = "";
        $data = [];
        $first = true;

        foreach ($result as $record) {
            if ($first) {
                $first = false;
                $x =
                    ' ["' .
                    $xName .
                    '", "' .
                    date($dbDateFormat, strtotime($record->DATE)) .
                    '"';
                $total = 0;
                foreach ($fieldsresult as $field) {
                    $data[] =
                        ' ["' .
                        $field->NAME .
                        '", ' .
                        $record->{$field->COLUMN_NAME};
                    $colors .=
                        '"' . $field->NAME . '": "' . $field->COLOR . '",';
                    $total += $record->{$field->COLUMN_NAME};
                }
                $data[] = ' ["Total", ' . $total;
                $colors .= '"Total": "Black"';
            } else {
                $x .=
                    ', "' . date($dbDateFormat, strtotime($record->DATE)) . '"';
                $total = 0;
                $i = 0;
                foreach ($fieldsresult as $field) {
                    $data[$i] .= ", " . $record->{$field->COLUMN_NAME};
                    $total += $record->{$field->COLUMN_NAME};
                    $i++;
                }
                $data[$i] .= ", " . $total;
            }
        }

        if (!$first) {
            $x .= "],";
            $columns = $x;
            $i = 0;
            foreach ($fieldsresult as $field) {
                $data[$i] .= "],";
                $columns .= $data[$i];
                $i++;
            }
            $data[$i] .= "]";
            $columns .= $data[$i];
        }

        $chart = [];
        $chart[] = $columns;
        $chart[] = $colors;
        return $chart;
    }

    /**
     * @return array
     */
    public static function getBrowsersOptions(): array
    {
        $options = [];
        $options[Self::EMPTY_VALUE_INT] = Self::EMPTY_OPTION;

        $result = Self::getCodeBrowsers();

        foreach ($result as $record) {
            $options[$record->BROWSER_CODE] = $record->BROWSER;
        }

        return $options;
    }

    /**
     * @return array
     */
    public static function getChartPeriodOptions(): array
    {
        $options = [];
        $options[1] = "Minutes";
        $options[2] = "Hours";
        $options[-1] = "Days";
        $options[3] = "Months";

        return $options;
    }

    /**
     * @return array
     */
    public static function getChartTypeOptions(): array
    {
        $options = [];
        $options[-1] = "Nb of pages per traffic";
        $options[1] = "Nb of pages per type";
        $options[2] = "Nb of pages per human/robot";
        $options[3] = "Nb of pages per clean/dirty";

        return $options;
    }

    /**
     * @return array
     */
    public static function getOptions(string $name): array
    {
        switch ($name) {
            case "browser":
                return Self::getBrowsersOptions();
            case "chartPeriod":
                return Self::getChartPeriodOptions();
            case "chartType":
                return Self::getChartTypeOptions();
            case "human":
                return Self::getHumanOptions();
            case "persona":
                return Self::getPersonasOptions();
            case "target":
                return Self::getTargetsOptions();
            case "traffic":
                return Self::getPersonasOptions();
            case "type":
                return Self::getTypesOptions();
            case "visit":
                return Self::getPersonasOptions();
            case "warning":
                return Self::getWarningsOptions();
            default:
                $options = [];
                $options[Self::EMPTY_VALUE_INT] = Self::EMPTY_OPTION;
                return $options;
        }
    }

    /**
     * @return array
     */
    public static function getHumanOptions(): array
    {
        $options = [];
        $options[Self::EMPTY_VALUE_INT] = Self::EMPTY_OPTION;

        $result = Self::getCodeHumans();

        foreach ($result as $record) {
            $options[$record->HUMAN_CODE] = $record->HUMAN;
        }

        return $options;
    }

    /**
     * @return array
     */
    public static function getPersonasOptions(): array
    {
        $options = [];
        $options[Self::EMPTY_VALUE_INT] = Self::EMPTY_OPTION;
        $result = Self::getCodePersonas();

        foreach ($result as $record) {
            $options[$record->PERSONA_CODE] = $record->PERSONA;
        }

        return $options;
    }

    /**
     * @return array
     */
    public static function getStatPagesRows(
        int    $target,
        int    $persona,
        int    $visit,
        int    $traffic,
        int    $type,
        int    $warning,
        string $visitor = Self::EMPTY_VALUE_STRING,
        string $parent = Self::EMPTY_VALUE_STRING
    ): array
    {
        $rows = [];
        $visitortPath = \Drupal::config("alberora.settings")->get(
            "statVisitorPage"
        );

        $result = Self::getStatPages(
            $target,
            $persona,
            $visit,
            $traffic,
            $type,
            $warning,
            $visitor,
            $parent
        );

        foreach ($result as $record) {
            $suffix = "";
            if (strlen($record->QUERY) > 25) {
                $suffix = "...";
            }

            array_push($rows, [
                $record->TAR_NAME,
                date("d/m/y H:i:s", strtotime($record->PAGE_DATE)),
                new FormattableMarkup(
                    '<a href=":link">@name</a>',
                    [
                        ":link" => $visitortPath . $record->PAGE_VIS_ID,
                        "@name" => ucfirst($record->VIS_NAME),
                    ]
                ),
                $record->PERSONA,
                new FormattableMarkup(
                    '<small>@name</small>',
                    [
                        "@name" => $record->VIT_NAME,
                    ]
                ),
                new FormattableMarkup(
                    '<a href=":link">@name</a>@NB',
                    [
                        ":link" => $record->PROTOCOL . "://" . $record->HOST . $record->PATH . "?" . $record->QUERY,
                        "@name" => $record->PATH,
                        "@NB" => ($record->NB <= 1 ? "" : " (x" . $record->NB . ")"),
                    ]
                ),
                new FormattableMarkup(
                    '<span title="@longText">@shortText</span>',
                    [
                        "@longText" => strtr($record->QUERY, "&", " "),
                        "@shortText" => substr($record->QUERY, 0, 25) . $suffix,
                    ]
                ),
                $record->TRAFFIC,
                $record->TYPE,
                $record->WARNING,
            ]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public static function getStatVisitorsRows(
        int $persona,
        int $human,
        int $browser
    ): array
    {
        $rows = [];
        $visitortPath = \Drupal::config("alberora.settings")->get(
            "statVisitorPage"
        );
        $parentPath = \Drupal::config("alberora.settings")->get(
            "statParentPage"
        );

        $result = Self::getStatVisitors($persona, $human, $browser);

        foreach ($result as $record) {
            $suffix = "";
            if (strlen($record->AGENT) > 25) {
                $suffix = "...";
            }

            array_push($rows, [
                new FormattableMarkup(
                    '<a href=":link">@name</a><BR><BR><a href=":link2"><small>@name2</small></a>',
                    [
                        ":link" => $visitortPath . $record->VIS_ID,
                        "@name" => $record->VIS_ID,
                        ":link2" => $parentPath . $record->PARENT_ID,
                        "@name2" => "(" . $record->PARENT_ID . ")",
                    ]
                ),
                new FormattableMarkup("<small>@name</small>", [
                    "@name" => $record->IPS,
                ]),
                ucwords($record->LOGINS),
                new FormattableMarkup("<small>@name</small>", [
                    "@name" => $record->UUIDS,
                ]),
                new FormattableMarkup(
                    '<span title="@longText">@shortText</span>',
                    [
                        "@longText" => $record->AGENT,
                        "@shortText" => substr($record->AGENT, 0, 25) . $suffix,
                    ]
                ),
                new FormattableMarkup(
                    "@persona<BR>@human<BR>@browser",
                    [
                        "@persona" => $record->PERSONA,
                        "@human" => $record->HUMAN,
                        "@browser" => $record->BROWSER,
                    ]
                ),
                new FormattableMarkup(
                    "@continent<BR>@country<BR>@region<BR>@city",
                    [
                        "@continent" => $record->CONTINENT,
                        "@country" => $record->COUNTRY,
                        "@region" => $record->REGION,
                        "@city" => $record->CITY,
                    ]
                ),
                date("d/m/y H:i:s", strtotime($record->LAST_UTD)),
            ]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public static function getStatLoginsRows(
        int $persona,
        int $human,
        int $browser
    ): array
    {
        $rows = [];
        $visitortPath = \Drupal::config("alberora.settings")->get(
            "statVisitorPage"
        );
        $parentPath = \Drupal::config("alberora.settings")->get(
            "statParentPage"
        );

        $result = Self::getStatLogins($persona, $human, $browser);

        foreach ($result as $record) {
            $suffix = "";
            if (strlen($record->AGENT) > 25) {
                $suffix = "...";
            }

            array_push($rows, [
                ucfirst($record->LOGIN),
                new FormattableMarkup(
                    '<a href=":link">@name</a><BR><BR><a href=":link2"><small>@name2</small></a>',
                    [
                        ":link" => $visitortPath . $record->VIS_ID,
                        "@name" => $record->VIS_ID,
                        ":link2" => $parentPath . $record->PARENT_ID,
                        "@name2" => "(" . $record->PARENT_ID . ")",
                    ]
                ),
                new FormattableMarkup("<small>@name</small>", [
                    "@name" => $record->IPS,
                ]),
                ucwords($record->LOGINS),
                new FormattableMarkup("<small>@name</small>", [
                    "@name" => $record->UUIDS,
                ]),
                new FormattableMarkup(
                    '<span title="@longText">@shortText</span>',
                    [
                        "@longText" => $record->AGENT,
                        "@shortText" => substr($record->AGENT, 0, 25) . $suffix,
                    ]
                ),
                new FormattableMarkup(
                    "@persona<BR>@human<BR>@browser",
                    [
                        "@persona" => $record->PERSONA,
                        "@human" => $record->HUMAN,
                        "@browser" => $record->BROWSER,
                    ]
                ),
                new FormattableMarkup(
                    "@continent<BR>@country<BR>@region<BR>@city",
                    [
                        "@continent" => $record->CONTINENT,
                        "@country" => $record->COUNTRY,
                        "@region" => $record->REGION,
                        "@city" => $record->CITY,
                    ]
                ),
                date("d/m/y H:i:s", strtotime($record->LAST_UTD)),
            ]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public static function getStatVisitorsDetailsOptions(
        string $login,
        string $name,
        string $uuid,
        string $ip,
        int    $persona,
        int    $human,
        int    $browser
    ): array
    {
        $rows = [];
        $visitortPath = \Drupal::config("alberora.settings")->get(
            "statVisitorPage"
        );
        $parentPath = \Drupal::config("alberora.settings")->get(
            "statParentPage"
        );

        $result = Self::getStatVisitorsDetails($login, $name, $uuid, $ip, $persona, $human, $browser);

        $i = 0;

        foreach ($result as $record) {
            $suffix = "";
            if (strlen($record->AGENT) > 25) {
                $suffix = "...";
            }

            $i++;

            $rows[$i] = [
                new FormattableMarkup(
                    '@name<BR><a href=":link"><small>@id</small></a>',
                    [
                        ":link" => $visitortPath . $record->VIS_ID,
                        "@name" => ucfirst($record->VIS_NAME),
                        "@id" => $record->VIS_ID,
                    ]
                ),
                new FormattableMarkup(
                    '@name<BR><a href=":link"><small>@id</small></a>',
                    [
                        ":link" => $parentPath . $record->PARENT_ID,
                        "@name" => ucfirst($record->PARENT_NAME),
                        "@id" => $record->PARENT_ID,
                    ]
                ),
                new FormattableMarkup("<small>@name</small>", [
                    "@name" => $record->IPS,
                ]),
                ucwords($record->LOGINS),
                new FormattableMarkup("<small>@name</small>", [
                    "@name" => $record->UUIDS,
                ]),
                new FormattableMarkup(
                    '<span title="@longText">@shortText</span>',
                    [
                        "@longText" => $record->AGENT,
                        "@shortText" => substr($record->AGENT, 0, 25) . $suffix,
                    ]
                ),
                new FormattableMarkup(
                    "@persona<BR>@human<BR>@browser",
                    [
                        "@persona" => $record->PERSONA,
                        "@human" => $record->HUMAN,
                        "@browser" => $record->BROWSER,
                    ]
                ),
                new FormattableMarkup(
                    "@continent<BR>@country<BR>@region<BR>@city",
                    [
                        "@continent" => $record->CONTINENT,
                        "@country" => $record->COUNTRY,
                        "@region" => $record->REGION,
                        "@city" => $record->CITY,
                    ]
                ),
                date("d/m/y H:i:s", strtotime($record->LAST_VISIT)),
            ];
        }

        return $rows;
    }

    /**
     * @return array
     */
    public static function getTargetsOptions(): array
    {
        $options = [];
        $options[Self::EMPTY_VALUE_INT] = Self::EMPTY_OPTION;
        $result = Self::getConfTargets();

        foreach ($result as $record) {
            $options[$record->TAR_ID] = $record->TAR_NAME;
        }

        return $options;
    }

    /**
     * @return array
     */
    public static function getTypesOptions(): array
    {
        $options = [];
        $options[Self::EMPTY_VALUE_INT] = Self::EMPTY_OPTION;
        $result = Self::getCodeTypes();

        foreach ($result as $record) {
            $options[$record->TYPE_CODE] = $record->TYPE;
        }

        return $options;
    }

    /**
     * @return array
     */
    public static function getWarningsOptions(): array
    {
        $options = [];
        $options[Self::EMPTY_VALUE_INT] = Self::EMPTY_OPTION;
        $options[Self::ANY_VALUE_INT] = Self::ANY_OPTION;

        $result = Self::getCodeWarnings();

        foreach ($result as $record) {
            $options[$record->WARNING_CODE] = $record->WARNING;
        }

        return $options;
    }

    /**
     * @return array
     */
    public static function getStatVisitorIPsRows(string $visitor): array
    {
        $rows = [];
        $result = Self::getStatIPsVisitor($visitor);
        $ipPath = \Drupal::config("alberora.settings")->get(
            "statIPPage"
        );

        foreach ($result as $record) {
            array_push($rows, [
                new FormattableMarkup(
                    '<a href=":link">@name</a>',
                    [
                        ":link" => $ipPath . $record->IP,
                        "@name" => $record->IP,
                    ]
                ),
                date("d/m/y H:i:s", strtotime($record->IP_START_DATE)),
                date("d/m/y H:i:s", strtotime($record->IP_END_DATE)),
            ]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public static function getStatVisitorLoginsRows(string $visitor): array
    {
        $rows = [];
        $result = Self::getStatLoginsVisitor($visitor);
        $loginPath = \Drupal::config("alberora.settings")->get(
            "statLoginPage"
        );

        foreach ($result as $record) {
            array_push($rows, [
                new FormattableMarkup(
                    '<a href=":link">@name</a>',
                    [
                        ":link" => $loginPath . $record->LOGIN,
                        "@name" => ucfirst($record->LOGIN),
                    ]
                ),
                date("d/m/y H:i:s", strtotime($record->LOGIN_START_DATE)),
                date("d/m/y H:i:s", strtotime($record->LOGIN_END_DATE)),
            ]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public static function getStatVisitorUUIDsRows(string $visitor): array
    {
        $rows = [];
        $result = Self::getStatUUIDsVisitor($visitor);
        $uuidPath = \Drupal::config("alberora.settings")->get(
            "statUUIDPage"
        );

        foreach ($result as $record) {
            array_push($rows, [
                new FormattableMarkup(
                    '<a href=":link">@name</a>',
                    [
                        ":link" => $uuidPath . $record->UUID,
                        "@name" => $record->UUID,
                    ]
                ),
                date("d/m/y H:i:s", strtotime($record->UUID_START_DATE)),
                date("d/m/y H:i:s", strtotime($record->UUID_END_DATE)),
            ]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public static function getStatVisitorTypesRows(string $visitor): array
    {
        $result = Self::getStatVisitorTypes($visitor);

        $rows = [];
        foreach ($result as $record) {
            array_push($rows, [$record->TYPE, $record->NB]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public static function getStatVisitorTypesPie(string $visitor): string
    {
        $result = Self::getStatVisitorTypes($visitor);
        $chart = Self::prepareChartPie($result);

        return StatCharts::displayChart(
            "StatVisitorTypesPie",
            $chart[0],
            $chart[1]
        );
    }

    /**
     * @return array
     */
    public static function getStatCleanChart(int $chartPeriod, int $target): string
    {
        $periods = Self::getPeriods($chartPeriod);
        $result = Self::getStatClean($periods[0], $target);
        $fieldsresult = Self::getCodeCleans();
        $chart = Self::prepareChartLine($result, $fieldsresult, $periods[1]);

        return StatCharts::displayChartWithX(
            "StatCleanGraph",
            "Date",
            $chart[0],
            $chart[1],
            $periods[2],
            "area-spline"
        );
    }

    /**
     * @return array
     */
    public static function getStatTrafficChart(int $chartPeriod, int $target): string
    {
        $periods = Self::getPeriods($chartPeriod);
        $result = Self::getStatTraffic($periods[0], $target);
        $fieldsresult = Self::getCodePersonas();
        $chart = Self::prepareChartLine($result, $fieldsresult, $periods[1]);

        return StatCharts::displayChartWithX(
            "StatTrafficGraph",
            "Date",
            $chart[0],
            $chart[1],
            $periods[2],
            "area-spline"
        );
    }

    /**
     * @return array
     */
    public static function getStatTypesChart(int $chartPeriod, int $target): string
    {
        $periods = Self::getPeriods($chartPeriod);
        $result = Self::getStatTypes($periods[0], $target);
        $fieldsresult = Self::getCodeTypes();
        $chart = Self::prepareChartLine($result, $fieldsresult, $periods[1]);

        return StatCharts::displayChartWithX(
            "StatTypesGraph",
            "Date",
            $chart[0],
            $chart[1],
            $periods[2],
            "area-spline"
        );
    }

    /**
     * @return string
     */
    public static function getStatHumanChart(int $chartPeriod, int $target): string
    {
        $periods = Self::getPeriods($chartPeriod);
        $result = Self::getStatHuman($periods[0], $target);
        $fieldsresult = Self::getCodeHumans();
        $chart = Self::prepareChartLine($result, $fieldsresult, $periods[1]);

        return StatCharts::displayChartWithX(
            "StatHumanGraph",
            "Date",
            $chart[0],
            $chart[1],
            $periods[2],
            "area-spline"
        );
    }

    /**
     * @return array
     */
    private static function getPeriods(int $chartPeriod): array
    {
        $periods = [];
        switch ($chartPeriod) {
            case 1:
                $periods[0] = "MINUTE";
                $periods[1] = "d/m/Y H:i";
                $periods[2] = "%d/%m/%Y %H:%M";
                break;
            case 2:
                $periods[0] = "HOUR";
                $periods[1] = "d/m/Y H:i";
                $periods[2] = "%d/%m/%Y %H:%M";
                break;
            case 3:
                $periods[0] = "MONTH";
                $periods[1] = "m/Y";
                $periods[2] = "%m/%Y";
                break;
            default:
                $periods[0] = "DAY";
                $periods[1] = "d/m/Y";
                $periods[2] = "%d/%m/%Y";
        }
        return $periods;
    }
}
