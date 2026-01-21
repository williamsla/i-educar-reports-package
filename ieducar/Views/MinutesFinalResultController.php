<?php

use App\Models\LegacyInstitution;
use App\Models\LegacySchoolClass;

class MinutesFinalResultController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 9998911;

    protected $_titulo = 'Relatório Ata de Resultado Final';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Emissão ata de resultado final', [
            url('educar_index.php') => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);

        $this->inputsHelper()->dynamic('situacaoMatricula', ['value' => 10]);

        $this->inputsHelper()->textArea('observacao', ['required' => false, 'label' => 'Observações', 'placeholder' => 'Utilize este espaço para exibir uma mensagem ou recado na ata de resutado final.']);

        // $helperOptions = ['objectName' => 'areaconhecimento'];
        // $options = [
        //     'label' => 'Áreas de conhecimento',
        //     'size' => 50,
        //     'required' => false,
        //     'placeholder' => 'Todas',
        //     'options' => ['value' => null]
        // ];
        //$this->inputsHelper()->multipleSearchAreasConhecimento('', $options, $helperOptions);
        
        $this->inputsHelper()->date('data_encerramento', [
            'placeholder' => '',
            'label' => 'Data de Encerramento',
            'value' => date('d/m/Y'),
            'required' => false
        ]);

        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new MinutesFinalResultReport();
    }

    public function beforeValidation()
    {
        $turmaId = (int) $this->getRequest()->ref_cod_turma;
        $serieId = (int) $this->getRequest()->ref_cod_serie;

        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', $serieId);
        $this->report->addArg('turma', $turmaId);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('observacao', $this->getRequest()->observacao);

        $areasConhecimento = $this->getRequest()->areaconhecimento ?? [];
        $areasConhecimento = implode(',', array_filter($areasConhecimento));

        $this->report->addArg('areas_conhecimento', trim($areasConhecimento) == '' ? 0 : $areasConhecimento);
        $this->report->addArg('filtro_areas_conhecimento', trim($areasConhecimento) == '');
        $this->report->addArg('data_encerramento', $this->getRequest()->data_encerramento);

        $temConceitoFixoEnv = getenv('TEM_CONCEITO_FIXO');
        $temConceitoFixo = ($temConceitoFixoEnv !== false && $temConceitoFixoEnv !== '') ? (bool) $temConceitoFixoEnv : false;
        $this->report->addArg('tem_conceito_fixo', $temConceitoFixo);

        $conceitoFixoEnv = getenv('CONCEITO_FIXO'); // APP ou PPC ou "" ou null
        $conceitoFixo = ($conceitoFixoEnv !== false && $conceitoFixoEnv !== '') ? (string) $conceitoFixoEnv : '';
        $this->report->addArg('conceito_fixo', $conceitoFixo);

        // Buscar o tipo de nota da série para direcionar o relatório adequado
        $tipoNota = 1; // Default: numérica
        try {
            $schoolClass = LegacySchoolClass::find($turmaId);
            if ($schoolClass) {
                $evaluationRule = $schoolClass->getEvaluationRule($serieId);
                if ($evaluationRule && isset($evaluationRule->tipo_nota) && $evaluationRule->tipo_nota !== null) {
                    // Forçar conversão para inteiro PHP puro
                    // O Eloquent pode retornar como string, então garantimos que seja int
                    $tipoNota = (int) $evaluationRule->tipo_nota;
                }
            }
        } catch (Exception $e) {
            // Em caso de erro, mantém o default
        }
        
        // Passar como número inteiro literal (igual ao ReportConceptualCardController que passa 2 diretamente)
        $this->report->addArg('tipo_nota', $tipoNota);
    }
}
