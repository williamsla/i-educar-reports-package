<?php

use Illuminate\Support\Facades\DB;

class ServantsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999820;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório cadastral de servidores';

    /**
     * @var int
     */
    public $periodo;

    /**
     * @var int
     */
    public $cod_servidor_funcao;

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório cadastral de servidores', [
            'educar_servidores_index.php' => 'Servidores',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', [
            'required' => false,
            'options' => ['multiple' => 8, 'label' => 'Série(s)'],
        ]);

        $this->inputsHelper()->dynamic('vinculo', ['required' => false]);
        
        $lista_funcoes = DB::table('pmieducar.funcao')->select('cod_funcao', 'nm_funcao')->where('ativo', 1)->get()->toArray();
        $opcoes = ['' => 'Selecione'];
        
        if ($lista_funcoes) {
            foreach ($lista_funcoes as $funcao) {
                $opcoes[$funcao->cod_funcao] = $funcao->nm_funcao;
            }
        }

        $periodo = [
            0 => 'Todos',
            1 => 'Matutino',
            2 => 'Vespertino',
            3 => 'Noturno'
        ];

        $this->campoLista('funcao', 'Fun&ccedil;&atilde;o', $opcoes, $this->cod_servidor_funcao, null, false, '', '', false, false);
        $this->campoLista('periodo', 'Per&iacute;odo', $periodo, $this->periodo, null, false, '', '', false, false);

        $modelo = [
            0 => 'Padrão',
            1 => 'Para assinatura'
        ];
        $this->campoRadio('modelo', 'Modelo', $modelo, $this->modelo ?? 0);
        $this->inputsHelper()->checkbox('emitir_totalizadores', ['label' => 'Adicionar totalizadores ao fim do relatório', 'value' => 1]);
        $this->inputsHelper()->checkbox('nao_emitir_afastados', ['label' => 'Não emitir servidores afastados', 'value' => 1]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $serieRequest = $this->getRequest()->ref_cod_serie_id
            ?? $this->getRequest()->ref_cod_serie
            ?? $_REQUEST['ref_cod_serie_id'] ?? null;
        $series = is_array($serieRequest)
            ? implode(',', array_filter(array_map('intval', $serieRequest)))
            : (int) ($serieRequest ?? 0);

        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', $series);
        $this->report->addArg('funcao', (int) $this->getRequest()->funcao);
        $this->report->addArg('vinculo', (int) $this->getRequest()->vinculo_id);
        $this->report->addArg('periodo', (int) $this->getRequest()->periodo);
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
        $this->report->addArg('emitir_totalizadores', (bool) $this->getRequest()->emitir_totalizadores);
        $this->report->addArg('nao_emitir_afastados', (bool) $this->getRequest()->nao_emitir_afastados);

        $filtros = [];
        $curso = (int) $this->getRequest()->ref_cod_curso;
        if ($curso) {
            $nome = DB::table('pmieducar.curso')->where('cod_curso', $curso)->value('nm_curso');
            $filtros[] = 'Curso: ' . ($nome ?: $curso);
        }
        if (!empty($series) && $series !== '0') {
            $ids = array_filter(array_map('intval', explode(',', (string) $series)));
            $nomes = DB::table('pmieducar.serie')->whereIn('cod_serie', $ids)->pluck('nm_serie');
            $filtros[] = 'Série(s): ' . $nomes->implode(', ');
        }
        $funcao = (int) $this->getRequest()->funcao;
        if ($funcao) {
            $nome = DB::table('pmieducar.funcao')->where('cod_funcao', $funcao)->value('nm_funcao');
            $filtros[] = 'Função: ' . ($nome ?: $funcao);
        }
        $vinculo = (int) $this->getRequest()->vinculo_id;
        if ($vinculo) {
            $nome = DB::table('portal.funcionario_vinculo')->where('cod_funcionario_vinculo', $vinculo)->value('nm_vinculo');
            $filtros[] = 'Vínculo: ' . ($nome ?: $vinculo);
        }
        $periodo = (int) $this->getRequest()->periodo;
        $periodos = [0 => 'Todos', 1 => 'Matutino', 2 => 'Vespertino', 3 => 'Noturno'];
        $filtros[] = 'Período: ' . ($periodos[$periodo] ?? 'Todos');

        $filtrosStr = implode(' | ', $filtros);
        if (!mb_check_encoding($filtrosStr, 'UTF-8')) {
            $filtrosStr = mb_convert_encoding($filtrosStr, 'UTF-8', 'ISO-8859-1');
        }
        $this->report->addArg('filtros_selecionados_b64', base64_encode($filtrosStr));
    }

    /**
     * @return ServantsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ServantsReport();
    }
}
