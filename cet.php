<?php

function arredondar_dois_decimal($valor) { 
   $float_arredondar=round($valor * 100) / 100; 
   return $float_arredondar; 
} 

function arredondar_dois_decimal_para_cima($valor) { 
   $float_arredondar=ceil($valor * 100) / 100; 
   return $float_arredondar; 
} 

function cet($numero_parcelas, $valor_emprestimo) {

        $numero_parcelas = $numero_parcelas;
        $taxa_ao_periodo = ((0.212895464763618 / 100) + 1);
        $parcela_atual = 1;
        $retorno = array();
        $mesInicial = date('m');
        $totalTaxaAoPeriodo = 0;
        $totalCalculo = 0;
        $emprestimo = $valor_emprestimo;
        $taxaMensal = (6.588 / 100) + 1;
        $iof = 0.000082;
        $iofFixo = $emprestimo * 0.0038;
        $saldo = $emprestimo;
        $totaliof = 0;

        while($parcela_atual <= $numero_parcelas){

            $n_dias_acumulativo = intval(round((mktime(0,0,0,date('m')+$parcela_atual,date('d'),date('Y')) - mktime(0,0,0,date('m'),date('d'),date('Y')))/86400));
            
            $retorno[$parcela_atual] = array(
                'data' => date('d/m/Y',mktime(0,0,0,date('m')+$parcela_atual,date('d'),date('Y'))), 
                'Numero de Dias' => $n_dias_acumulativo,
                'Dias do Mês' => date('t',strtotime(date('Y-m-d',mktime(0,0,0,(date('m')+$parcela_atual)-1,date('d'),date('Y'))))),
                'Taxa ao Periodo' => pow($taxa_ao_periodo,$n_dias_acumulativo)
            );

            $totalTaxaAoPeriodo = ( $totalTaxaAoPeriodo == 0 ? pow($taxa_ao_periodo,$n_dias_acumulativo) : $totalTaxaAoPeriodo * pow($taxa_ao_periodo,$n_dias_acumulativo) ) ;

            $parcela_atual++;

        }

        foreach($retorno AS $parcela_atual => $dados){

            $retorno[$parcela_atual]['Cálculo'] = ($totalTaxaAoPeriodo / $retorno[$parcela_atual]['Taxa ao Periodo']);
            $totalCalculo += $retorno[$parcela_atual]['Cálculo'];

        }

        $prestacao = (($totalTaxaAoPeriodo * $emprestimo) / $totalCalculo);

        foreach($retorno AS $parcela_atual => $dados){
            
            $juros              = ((pow($taxaMensal,$retorno[$parcela_atual]['Dias do Mês']/30))-1)*$saldo;
            $amortizacao        = ($prestacao - $juros);
            $saldo             -= ($amortizacao);
            
            
            $retorno[$parcela_atual]['Juros'] = arredondar_dois_decimal($juros);
            $retorno[$parcela_atual]['Amortização'] = arredondar_dois_decimal($amortizacao);
            $retorno[$parcela_atual]['Saldo'] = arredondar_dois_decimal($saldo);
            
            if($retorno[$parcela_atual]['Numero de Dias'] > 365) {

                $calculoiof = ($amortizacao * $iof * 365);

                $retorno[$parcela_atual]['IOF'] = ($calculoiof);

            }else {

                $calculoiof = ($amortizacao * $iof * $retorno[$parcela_atual]['Numero de Dias']);

                $retorno[$parcela_atual]['IOF'] = arredondar_dois_decimal_para_cima($calculoiof);
            
            }

            $retorno[$parcela_atual]['Prestação'] = $prestacao;

            $totaliof += $retorno[$parcela_atual]['IOF'];

        }

        $retorno['Configurações'] = array(
            'Número de Parcelas' => $numero_parcelas
        );

        $retorno['Dados Finais'] = array(
            'Valor Prestacao' => number_format($prestacao,2),   
            'IOF Total' => arredondar_dois_decimal($totaliof + $iofFixo), 
            'Valor Financiado' => ($emprestimo + arredondar_dois_decimal($totaliof + $iofFixo)), 
            'Prestação com Juros' => number_format((($emprestimo + arredondar_dois_decimal($totaliof + $iofFixo)) * $totalTaxaAoPeriodo) / $totalCalculo,2), 
            'Valor com Juros' => (((($emprestimo + arredondar_dois_decimal($totaliof + $iofFixo)) * $totalTaxaAoPeriodo) / $totalCalculo) * $numero_parcelas)
        );

        return $retorno;

}

?>