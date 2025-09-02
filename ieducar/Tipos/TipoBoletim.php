<?php

class Portabilis_Model_Report_TipoBoletim extends CoreExt_Enum
{
    const NUMERIC = 1;
    const CONCEPTUAL = 2;
    const CONCEPTUAL_LANDSCAPE = 3;
    const PARECER_DESCRITIVO_COMPONENTE = 9;
    const PARECER_DESCRITIVO_GERAL = 10;
    const NUMERIC_WITH_SEMESTER_RECOVERY = 11;

    protected $_data = [
        self::NUMERIC => 'Boletim numérico',
        self::CONCEPTUAL => 'Boletim conceitual',
        self::PARECER_DESCRITIVO_COMPONENTE => 'Parecer descritivo por componente',
        self::PARECER_DESCRITIVO_GERAL => 'Parecer descritivo geral',
        self::NUMERIC_WITH_SEMESTER_RECOVERY => 'Boletim numérico com recuperação semestral',
    ];

    protected $_reports = [
        self::NUMERIC => 'report-card',
        self::CONCEPTUAL => 'conceptual-report-card',
        self::CONCEPTUAL_LANDSCAPE => 'conceptual-landscape-report-card',
        self::PARECER_DESCRITIVO_COMPONENTE => 'descriptive-opinion-report-card',
        self::PARECER_DESCRITIVO_GERAL => 'general-opinion-report-card',
        self::NUMERIC_WITH_SEMESTER_RECOVERY => 'report-card-with-semester-recovery',
    ];

    public function getReports()
    {
        $dominio = $_SERVER['HTTP_HOST'];
        if (strpos($dominio, 'japaratinga') !== false) {
            $this->_reports[self::CONCEPTUAL] = 'conceptual-report-card-annual';
        }

        return $this->_reports;
    }

    public static function getInstance()
    {
        return self::_getInstance(__CLASS__);
    }
}
