<?php

/**
 * @file
 * Contains \Drupal\alberora\Database.
 */

namespace Drupal\alberora\Database;

use Drupal\Core\Database\Database;
use Drupal\Component\Render\FormattableMarkup;

use Drupal\alberora\Log\StatLog;

/**
 *
 */
class StatDatabaseQueries
{
    const STAT_DATABASE = "statdatabase";
    const EMPTY_VALUE_INT = -1;
    const EMPTY_VALUE_STRING = "";
    const EMPTY_OPTION = "--- Select ---";
    const ANY_VALUE_INT = -2;
    const ANY_OPTION = "*** Any value ***";

    /**
     * @return array
     */
    public static function getCodeBrowsers(): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->select("CODE_BROWSER", "cb")
            ->fields("cb")
            ->orderBy("BROWSER_CODE");
        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getCodeCleans(): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->select("CODE_CLEAN", "cc")
            ->fields("cc")
            ->orderBy("CLEAN_CODE");
        $query->addField("cc", "CLEAN", "NAME");
        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getCodeHumans(): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->select("CODE_HUMAN", "ch")
            ->fields("ch")
            ->orderBy("HUMAN_CODE");
        $query->addField("ch", "HUMAN", "NAME");
        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getCodePersonas(): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->select("CODE_PERSONA", "cp")
            ->fields("cp")
            ->orderBy("PERSONA_CODE");
        $query->addField("cp", "PERSONA", "NAME");
        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getCodeTypes(): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->select("CODE_TYPE", "ct")
            ->fields("ct")
            ->orderBy("TYPE_CODE");
        $query->addField("ct", "TYPE", "NAME");
        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getCodeWarnings(): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->select("CODE_WARNING", "cw")
            ->fields("cw")
            ->orderBy("WARNING_CODE");
        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getConfTargets(): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("CONF_TARGET", "ct");
        $query->join("V_ACCESS_TARGET", "va", "va.TAR_ID = ct.TAR_ID");
        $query
            ->fields("ct")
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->orderBy("TAR_NAME");
        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatIP(string $ip): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("V_STAT_IPS", "vsi");
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vsi.ACC_ID");
        $query
            ->fields("vsi", [
                "IP", "START_DATE", "END_DATE"
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("vsi.IP", $ip . "%", "LIKE");

        $result = $query->execute()->fetchAssoc();

        Database::setActiveConnection();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatIPsVisitor(string $visitor): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("STAT_IP", "si");
        $query->join("V_ACCESS_VISITOR", "va", "va.VIS_ID = si.IP_VIS_ID");
        $query
            ->fields("si", ["IP", "IP_START_DATE", "IP_END_DATE"])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("si.IP_VIS_ID", $visitor, "=")
            ->orderBy("si.IP_END_DATE", "DESC")
            ->range(0, 25);

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatLogin(string $login): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("V_STAT_LOGINS", "vsl");
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vsl.ACC_ID");
        $query
            ->fields("vsl", [
                "LOGIN", "START_DATE", "END_DATE"
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("vsl.LOGIN", $login, "=");

        $result = $query->execute()->fetchAssoc();

        Database::setActiveConnection();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatName(string $name): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("V_STAT_VISITOR_NAME", "vsvn");
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vsvn.ACC_ID");
        $query
            ->fields("vsvn", [
                "VIS_NAME", "START_DATE", "END_DATE"
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("vsvn.VIS_NAME", $name, "=");

        $result = $query->execute()->fetchAssoc();

        Database::setActiveConnection();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatlogins(
        int $persona,
        int $human,
        int $browser): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("V_STAT_VISITOR", "vsv");
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vsv.VIS_ACC_ID");
        $query
            ->fields("vsv", [
                "VIS_ID",
                "PARENT_ID",
                "IPS",
                "LOGINS",
                "UUIDS",
                "AGENT",
                "PERSONA_CODE",
                "PERSONA",
                "LAST_VISIT",
                "UTD",
                "LAST_UTD",
                "HUMAN",
                "HUMAN_CODE",
                "BROWSER",
                "BROWSER_CODE",
                "CONTINENT",
                "COUNTRY",
                "REGION",
                "CITY",
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->orderBy("vsv.LAST_UTD", "DESC")
            ->range(0, 25);

        if ($persona != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.PERSONA_CODE", $persona, "=");
        }

        if ($human != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.HUMAN_CODE", $human, "=");
        }

        if ($browser != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.BROWSER_CODE", $browser, "=");
        }

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatLoginsVisitor(string $visitor): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("STAT_LOGIN", "sl");
        $query->join("V_ACCESS_VISITOR", "va", "va.VIS_ID = sl.LOGIN_VIS_ID");
        $query
            ->fields("sl", ["LOGIN", "LOGIN_START_DATE", "LOGIN_END_DATE"])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("sl.LOGIN_VIS_ID", $visitor, "=")
            ->orderBy("sl.LOGIN_END_DATE", "DESC")
            ->range(0, 25);

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatPages(
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
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("V_STAT_PAGE", "vsp");
        $query->join("V_ACCESS_TARGET", "va", "va.TAR_ID = vsp.TAR_ID");
        $query
            ->fields("vsp", [
                "TAR_NAME",
                "PROTOCOL",
                "HOST",
                "PAGE_DATE",
                "PAGE_VIS_ID",
                "VIS_NAME",
                "PERSONA",
                "PAGE_PARENT_ID",
                "PAGE_VIT_ID",
                "VIT_NAME",
                "VIT_PERSONA",
                "NB",
                "PATH",
                "QUERY",
                "TRAFFIC",
                "TYPE",
                "WARNING",
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->orderBy("PAGE_DATE", "DESC")
            ->range(0, 25);

        if ($target != Self::EMPTY_VALUE_INT) {
            $query->condition("vsp.TAR_ID", $target, "=");
        }

        if ($persona != Self::EMPTY_VALUE_INT) {
            $query->condition("vsp.PERSONA_CODE", $persona, "=");
        }

        if ($visit != Self::EMPTY_VALUE_INT) {
            $query->condition("vsp.VIT_PERSONA_CODE", $visit, "=");
        }

        if ($traffic != Self::EMPTY_VALUE_INT) {
            $query->condition("vsp.TRAFFIC_CODE", $traffic, "=");
        }

        if ($type != Self::EMPTY_VALUE_INT) {
            $query->condition("vsp.TYPE_CODE", $type, "=");
        }

        if ($warning == Self::ANY_VALUE_INT) {
            // NO Warning = 1
            $query->condition("vsp.WARNING_CODE", 1, "!=");
        } elseif ($warning != Self::EMPTY_VALUE_INT) {
            $query->condition("vsp.WARNING_CODE", $warning, "=");
        }

        if ($visitor != Self::EMPTY_VALUE_STRING) {
            $query->condition("vsp.PAGE_VIS_ID", $visitor . "%", "LIKE");
        }

        if ($parent != Self::EMPTY_VALUE_STRING) {
            $query->condition("vsp.PAGE_PARENT_ID", $parent . "%", "LIKE");
        }

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatUUID(string $uuid): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("V_STAT_UUIDS", "vsu");
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vsu.ACC_ID");
        $query
            ->fields("vsu", [
                "UUID", "START_DATE", "END_DATE"
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("vsu.UUID", $uuid, "=");

        $result = $query->execute()->fetchAssoc();

        Database::setActiveConnection();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatUUIDsVisitor(string $visitor): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("STAT_UUID", "su");
        $query->join("V_ACCESS_VISITOR", "va", "va.VIS_ID = su.UUID_VIS_ID");
        $query
            ->fields("su", ["UUID", "UUID_START_DATE", "UUID_END_DATE"])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("su.UUID_VIS_ID", $visitor, "=")
            ->orderBy("su.UUID_END_DATE", "DESC")
            ->range(0, 25);

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatVisitor(string $visitor): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("V_STAT_VISITOR", "vsv");
        $query->join("V_ACCESS_VISITOR", "va", "va.VIS_ID = vsv.VIS_ID");
        $query
            ->fields("vsv", [
                "VIS_ACC_ID",
                "VIS_ID",
                "VIS_NAME",
                "IP",
                "HOST",
                "AGENT",
                "LAST_VISIT",
                "PERSONA",
                "UTD",
                "LAST_UTD",
                "CONTINENT",
                "COUNTRY",
                "REGION",
                "CITY",
                "BROWSER",
                "PERSONA_CODE",
                "BROWSER_CODE",
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("vsv.VIS_ID", $visitor, "=");

        $result = $query->execute()->fetchAssoc();

        Database::setActiveConnection();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatVisitors(
        int $persona,
        int $human,
        int $browser): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select("V_STAT_VISITOR", "vsv");
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vsv.VIS_ACC_ID");
        $query
            ->fields("vsv", [
                "VIS_ID",
                "PARENT_ID",
                "IPS",
                "LOGINS",
                "UUIDS",
                "AGENT",
                "PERSONA_CODE",
                "PERSONA",
                "LAST_VISIT",
                "UTD",
                "LAST_UTD",
                "HUMAN",
                "HUMAN_CODE",
                "BROWSER",
                "BROWSER_CODE",
                "CONTINENT",
                "COUNTRY",
                "REGION",
                "CITY",
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->orderBy("vsv.LAST_UTD", "DESC")
            ->range(0, 25);

        if ($persona != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.PERSONA_CODE", $persona, "=");
        }

        if ($human != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.HUMAN_CODE", $human, "=");
        }

        if ($browser != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.BROWSER_CODE", $browser, "=");
        }

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatVisitorsDetails(
        string $login,
        string $name,
        string $uuid,
        string $ip,
        int    $persona,
        int    $human,
        int    $browser): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        if ($login != Self::EMPTY_VALUE_STRING) {
            $query = Database::getConnection()->select("V_STAT_VISITOR_LOGIN", "vsv");
        } elseif ($name != Self::EMPTY_VALUE_STRING) {
            $query = Database::getConnection()->select("V_STAT_VISITOR", "vsv");
        } elseif ($uuid != Self::EMPTY_VALUE_STRING) {
            $query = Database::getConnection()->select("V_STAT_VISITOR_UUID", "vsv");
        } else {
            $query = Database::getConnection()->select("V_STAT_VISITOR_IP", "vsv");
        }

        $query->join("V_ACCESS_VISITOR", "va", "va.VIS_ID = vsv.VIS_ID");
        $query
            ->fields("vsv", [
                "VIS_ID",
                "VIS_NAME",
                "PARENT_ID",
                "PARENT_NAME",
                "IPS",
                "LOGINS",
                "UUIDS",
                "AGENT",
                "PERSONA_CODE",
                "PERSONA",
                "LAST_VISIT",
                "HUMAN",
                "HUMAN_CODE",
                "BROWSER",
                "BROWSER_CODE",
                "CONTINENT",
                "COUNTRY",
                "REGION",
                "CITY",
            ])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->orderBy("vsv.LAST_VISIT", "DESC");

        if ($login != Self::EMPTY_VALUE_STRING) {
            $query->condition("vsv.LOGIN", $login, "=");
        } elseif ($name != Self::EMPTY_VALUE_STRING) {
            $query->condition("vsv.VIS_NAME", $name, "=");
        } elseif ($uuid != Self::EMPTY_VALUE_STRING) {
            $query->condition("vsv.UUID", $uuid, "=");
        } else {
            $query->condition("vsv.IP", $ip . "%", "LIKE");
        }

        if ($persona != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.PERSONA_CODE", $persona, "=");
        }

        if ($human != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.HUMAN_CODE", $human, "=");
        }

        if ($browser != Self::EMPTY_VALUE_INT) {
            $query->condition("vsv.BROWSER_CODE", $browser, "=");
        }

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatVisitorTypes(string $visitor): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select(
            "V_CHART_VISITOR_TYPES",
            "vsvt"
        );
        $query->join("V_ACCESS_VISITOR", "va", "va.VIS_ID = vsvt.VIS_ID");
        $query
            ->fields("vsvt", ["VIS_ID", "TYPE", "COLOR", "NB"])
            ->condition("va.USER_ID", \Drupal::currentUser()->id(), "=")
            ->condition("vsvt.VIS_ID", $visitor, "=")
            ->orderBy("vsvt.TYPE_CODE", "ASC")
            ->range(0, 25);

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatClean(string $period, int $target): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select(
            "V_CHART_PAGE_CLEAN_" . $period,
            "vcpc"
        );
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vcpc.ACC_ID");
        $query->addField("vcpc", "DATE", "DATE");
        $query->addExpression("SUM(CLEAN)", "CLEAN");
        $query->addExpression("SUM(DURTY)", "DURTY");
        $query->condition("va.USER_ID", \Drupal::currentUser()->id(), "=");

        if ($target != Self::EMPTY_VALUE_INT) {
            $query->condition("vcpc.TAR_ID", $target, "=");
        }

        $query->groupBy("vcpc.DATE");

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatTraffic(string $period, int $target): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select(
            "V_CHART_PAGE_TRAFFIC_" . $period,
            "vcpt"
        );
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vcpt.ACC_ID");
        $query->addField("vcpt", "DATE", "DATE");
        $query->addExpression("SUM(ADMIN)", "ADMIN");
        $query->addExpression("SUM(TRUSTED)", "TRUSTED");
        $query->addExpression("SUM(NORMAL)", "NORMAL");
        $query->addExpression("SUM(SUSPECT)", "SUSPECT");
        $query->addExpression("SUM(MALICIOUS)", "MALICIOUS");
        $query->condition("va.USER_ID", \Drupal::currentUser()->id(), "=");

        if ($target != Self::EMPTY_VALUE_INT) {
            $query->condition("vcpt.TAR_ID", $target, "=");
        }

        $query->groupBy("vcpt.DATE");

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatTypes(string $period, int $target): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select(
            "V_CHART_PAGE_TYPE_" . $period,
            "vcpt"
        );
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vcpt.ACC_ID");
        $query->addField("vcpt", "DATE", "DATE");
        $query->addExpression("SUM(HIDDEN)", "HIDDEN");
        $query->addExpression("SUM(STANDARD)", "STANDARD");
        $query->addExpression("SUM(DOWNLOAD)", "DOWNLOAD");
        $query->addExpression("SUM(LOGIN)", "LOGIN");
        $query->addExpression("SUM(FORM)", "FORM");
        $query->addExpression("SUM(NOTFOUND)", "NOTFOUND");
        $query->addExpression("SUM(NOACCESS)", "NOACCESS");
        $query->addExpression("SUM(CSP)", "CSP");
        $query->addExpression("SUM(BLOCKED)", "BLOCKED");
        $query->condition("va.USER_ID", \Drupal::currentUser()->id(), "=");

        if ($target != Self::EMPTY_VALUE_INT) {
            $query->condition("vcpt.TAR_ID", $target, "=");
        }

        $query->groupBy("vcpt.DATE");
        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    /**
     * @return array
     */
    public static function getStatHuman(string $period, int $target): array
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()->select(
            "V_CHART_PAGE_HUMAN_" . $period,
            "vcph"
        );
        $query->join("V_ACCESS_ACCOUNT", "va", "va.ACC_ID = vcph.ACC_ID");
        $query->addField("vcph", "DATE", "DATE");
        $query->addExpression("SUM(HUMAN)", "HUMAN");
        $query->addExpression("SUM(ROBOT)", "ROBOT");
        $query->condition("va.USER_ID", \Drupal::currentUser()->id(), "=");

        if ($target != Self::EMPTY_VALUE_INT) {
            $query->condition("vcph.TAR_ID", $target, "=");
        }

        $query->groupBy("vcph.DATE");

        $result = $query->execute()->fetchAll();

        Database::setActiveConnection();

        return $result;
    }


    /**
     * @param string $visitor
     * @param int $persona
     * @return void
     */
    public static function saveStatVisitorPersona(string $visitor, int $persona)
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->update("STAT_VISITOR")
            ->fields([
                "PERSONA_CODE" => $persona,
            ])
            ->condition("VIS_ID", $visitor, "=")
            ->execute();

        $query = Database::getConnection()
            ->update("STAT_VISIT")
            ->fields([
                "PERSONA_CODE" => $persona,
            ])
            ->expression("VIT_NAME", "SUBSTRING(VIT_NAME,1,14)")
            ->condition("VIT_VIS_ID", $visitor, "=")
            ->execute();

        $query = Database::getConnection()
            ->update("STAT_VISIT")
            ->expression("VIT_NAME", "CONCAT(VIT_NAME, ' (A)')")
            ->condition("VIT_VIS_ID", $visitor, "=")
            ->condition("PERSONA_CODE", 1, "=")
            ->execute();

        $query = Database::getConnection()
            ->update("STAT_VISIT")
            ->expression("VIT_NAME", "CONCAT(VIT_NAME, ' (T)')")
            ->condition("VIT_VIS_ID", $visitor, "=")
            ->condition("PERSONA_CODE", 3, "=")
            ->execute();

        $query = Database::getConnection()
            ->update("STAT_VISIT")
            ->expression("VIT_NAME", "CONCAT(VIT_NAME, ' (S)')")
            ->condition("VIT_VIS_ID", $visitor, "=")
            ->condition("PERSONA_CODE", 7, "=")
            ->execute();

        $query = Database::getConnection()
            ->update("STAT_VISIT")
            ->expression("VIT_NAME", "CONCAT(VIT_NAME, ' (M)')")
            ->condition("VIT_VIS_ID", $visitor, "=")
            ->condition("PERSONA_CODE", 9, "=")
            ->execute();

        $query = Database::getConnection()
            ->update("STAT_PAGE")
            ->fields([
                "TRAFFIC_CODE" => $persona,
            ])
            ->condition("PAGE_VIS_ID", $visitor, "=")
            ->execute();

        Database::setActiveConnection();
    }

    /**
     * @param string $visitor
     * @param int $browser
     * @return void
     */
    public static function saveStatVisitorBrowser(string $visitor, int $browser)
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->update("STAT_VISITOR")
            ->fields([
                "BROWSER_CODE" => $browser,
            ])
            ->condition("VIS_ID", $visitor, "=")
            ->execute();

        Database::setActiveConnection();
    }

    /**
     * @param string $visitor
     * @param string $name
     * @return void
     */
    public static function saveStatVisitorName(string $visitor, string $name)
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        $query = Database::getConnection()
            ->update("STAT_VISITOR")
            ->fields([
                "VIS_NAME" => $name,
            ]);

        $vORGroup = $query->orConditionGroup()
            ->condition("VIS_ID", $visitor, "=")
            ->condition("PARENT_ID", $visitor, "=");

        $query->condition($vORGroup)
            ->execute();

        Database::setActiveConnection();
    }

    /**
     * @param array $visitorsList
     * @param string $parent
     * @return void
     */
    public static function mergeVisitors(array $visitorsList, string $parent)
    {
        Database::setActiveConnection(self::STAT_DATABASE);

        // Update the parent of the selected visitors
        $query = Database::getConnection()
            ->update("STAT_VISITOR")
            ->fields([
                "PARENT_ID" => $parent,
            ])
            ->condition("VIS_ID", $visitorsList, "IN")
            ->execute();

        // Also update the parent of Visitors whose parent was updated
        $query = Database::getConnection()
            ->update("STAT_VISITOR")
            ->fields([
                "PARENT_ID" => $parent,
            ])
            ->condition("PARENT_ID", $visitorsList, "IN")
            ->execute();

        // Retrieve the parent's name
        $query = Database::getConnection()->select(
            "V_STAT_PARENT_NAME",
            "vsvn"
        );
        $query
            ->fields("vsvn", [
                "VIS_NAME",
            ])
            ->condition("PARENT_ID", $parent, "=");
        $parentName = $query->execute()->fetchField();

        // Update the name of kids
        $query = Database::getConnection()
            ->update("STAT_VISITOR")
            ->fields([
                "VIS_NAME" => $parentName,
            ])
            ->condition("PARENT_ID", $parent, "=")
            ->execute();

        Database::setActiveConnection();
    }
}
