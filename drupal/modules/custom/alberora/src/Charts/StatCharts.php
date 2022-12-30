<?php

/**
 * @file
 * Contains \Drupal\alberora\Charts.
 */

namespace Drupal\alberora\Charts;

/**
 *
 */
class StatCharts
{
    /**
     * @return string
     */
    public static function displayChart(
        string $name,
        string $columns,
        string $colors = "",
        string $charType = "donut"
    ): string
    {
        $script = "<script>";
        $script .= "var chart = bb.generate({";
        $script .= '  "data": {';
        $script .= '    "columns": [';
        $script .= $columns;
        $script .= "    ], ";
        if ($colors != "") {
            $script .= '    "colors": {';
            $script .= $colors;
            $script .= "    },";
        }
        $script .= '    "type": "' . $charType . '"';
        $script .= "  },";
        $script .= '  "donut": {';
        $script .= '    "label": {';
        $script .=
            '      "format": function(value, ratio, id) { return value; }';
        $script .= "    }";
        $script .= "  },";
        $script .= '  "pie": {';
        $script .= '    "label": {';
        $script .=
            '      "format": function(value, ratio, id) { return value; }';
        $script .= "    }";
        $script .= "  },";
        $script .= '  "bindto": "#' . $name . '"';
        $script .= "  });";
        $script .= "</script>";

        return $script;
    }

    /**
     * @return string
     */
    public static function displayChartWithX(
        string $name,
        string $xName,
        string $columns,
        string $colors = "",
        string $chartDateFormat = "%d/%m/%Y",
        string $charType = "spline"
    ): string
    {
        $script = "<script>";
        $script .= "var chart = bb.generate({";
        $script .= '  "data": {';
        $script .= '    "x": "' . $xName . '", ';
        $script .= '    "xFormat": "' . $chartDateFormat . '",';
        $script .= "    columns: [";
        $script .= $columns;
        $script .= "    ], ";
        if ($colors != "") {
            $script .= '    "colors": {';
            $script .= $colors;
            $script .= "    },";
        }
        $script .= '    "type": "' . $charType . '"';
        $script .= "  },";
        $script .= "  axis: {";
        $script .= "    x: {";
        $script .= '      type: "timeseries"';
        $script .= "    }";
        $script .= "  },";
        $script .= '  "bindto": "#' . $name . '"';
        $script .= "  });";
        $script .= "</script>";

        return $script;
    }
}
