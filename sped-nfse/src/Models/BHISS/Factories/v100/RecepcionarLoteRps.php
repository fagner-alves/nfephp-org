<?php

namespace NFePHP\NFSe\Models\BHISS\Factories\v100;

use NFePHP\Common\DOMImproved as Dom;
use NFePHP\NFSe\Models\Abrasf\Factories\RecepcionarLoteRps as RecepcionarLoteRpsBase;
use NFePHP\NFSe\Models\Abrasf\Factories\Signer;


class RecepcionarLoteRps extends RecepcionarLoteRpsBase
{
    /**
     * Método usado para gerar o XML do Soap Request
     * @param $versao
     * @param $remetenteTipoDoc
     * @param $remetenteCNPJCPF
     * @param $inscricaoMunicipal
     * @param $lote
     * @param $rpss
     * @return string
     */
    public function render(
        $versao,
        $remetenteTipoDoc,
        $remetenteCNPJCPF,
        $inscricaoMunicipal,
        $lote,
        $rpss
    ) {
        $method = 'EnviarLoteRpsEnvio';
        $xsd = "nfse_v{$versao}";
        $qtdRps = count($rpss);


        $dom = new Dom('1.0', 'utf-8');
        $dom->formatOutput = false;
        //Cria o elemento pai
        $root = $dom->createElement('EnviarLoteRpsEnvio');
        $root->setAttribute('xmlns', $this->xmlns);

        //Adiciona as tags ao DOM
        $dom->appendChild($root);

        $loteRps = $dom->createElement('LoteRps');
        $loteRps->setAttribute('Id', "Lote{$lote}");
        $loteRps->setAttribute('versao', "1.00");

        $dom->appChild($root, $loteRps, 'Adicionando tag LoteRps a EnviarLoteRpsEnvio');


        $dom->addChild(
            $loteRps,
            'NumeroLote',
            $lote,
            true,
            "Numero do lote RPS",
            true
        );

        /* CPF CNPJ */
        //Adiciona o Cpf/Cnpj na tag CpfCnpj
        $dom->addChild(
            $loteRps,
            'Cnpj',
            $remetenteCNPJCPF,
            true,
            "Cpf / Cnpj",
            true
        );

        /* Inscrição Municipal */
        $dom->addChild(
            $loteRps,
            'InscricaoMunicipal',
            $inscricaoMunicipal,
            true,
            "Inscricao Municipal",
            false
        );

        /* Quantidade de RPSs */
        $dom->addChild(
            $loteRps,
            'QuantidadeRps',
            $qtdRps,
            true,
            "Quantidade de Rps",
            true
        );

        /* Lista de RPS */
        $listaRps = $dom->createElement('ListaRps');
        $dom->appChild($loteRps, $listaRps, 'Adicionando tag ListaRps a LoteRps');

        foreach ($rpss as $rps) {
            RenderRps::appendRps($rps, $this->timezone, $this->certificate, $this->algorithm, $dom, $listaRps);
        }


        //Parse para XML
        $xml = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $dom->saveXML());

        $body = Signer::sign(
            $this->certificate,
            $xml,
            'LoteRps',
            'Id',
            $this->algorithm,
            [false, false, null, null],
            '',
            true
        );
        $body = $this->clear($body);
        $this->validar($versao, $body, $this->schemeFolder, $xsd, '', $this->cmun);

        return $body;
    }
}
