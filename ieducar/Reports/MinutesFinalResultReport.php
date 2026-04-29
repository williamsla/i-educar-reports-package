<?php

use iEducar\Reports\JsonDataSource;

class MinutesFinalResultReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public $modifiers = [
        MinutesFinalResultModifier::class
    ];

    public function templateName()
    {
        // Prioridade 2: Verificar o tipo de nota da série
        $tipoNota = isset($this->args['tipo_nota']) ? (int) $this->args['tipo_nota'] : 1;
        $temConceitoFixoArg = $this->args['tem_conceito_fixo'] ?? false;
        $tem_conceito_fixo = filter_var($temConceitoFixoArg, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $tem_conceito_fixo = $tem_conceito_fixo ?? false;
                        
        // tipo_nota = 2 significa conceitual
        if ($tipoNota == 0 || ($tipoNota == 2 && $tem_conceito_fixo)) {
            return 'minutes-final-result-with-fixed-concept';
        } elseif ($tipoNota == 2 && !$tem_conceito_fixo) {
            return 'minutes-final-result-with-concept';
        }

        return 'minutes-final-result';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
        $this->addRequiredArg('turma');
    }

    public function getJsonData()
    {
        return [
            'main' => (new QueryMinutesFinalResult())->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
