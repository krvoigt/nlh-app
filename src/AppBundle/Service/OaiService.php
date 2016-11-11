<?php

declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Exception\OaiException;
use Solarium\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class OaiService
{
    const EXPIRATION_DATE = 259200;

    private $conf;

    /**
     * @var bool
     */
    private $hideCollections = false;

    /**
     * @var \DOMDocument
     */
    public $oai;

    /**
     * @var \DOMElement
     */
    private $oai_pmh;

    /**
     * @var \DOMElement
     */
    private $request;

    /**
     * @var \DOMElement
     */
    private $record;

    /**
     * @var \DOMElement
     */
    private $node;

    /**
     * @var \DOMElement
     */
    private $head;

    /**
     * @var \DOMElement
     */
    private $oai_dc;

    /**
     * @var \DOMElement
     */
    private $metadata;

    /**
     * @var \DOMElement
     */
    private $ListRecords;

    /**
     * @var string
     */
    private $kernelRootDir;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $oaiTempDirectory;

    /**
     * @var Client
     */
    private $client;

    private $oaiPma = [
        'xmlns' => 'http://www.openarchives.org/OAI/2.0/',
        'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        'xsi:schemaLocation' => 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd',
    ];

    private $verbs = [
        'GetRecord' => [
            'allowedArguments' => 'identifier,metadataPrefix',
            'requiredArguments' => 'identifier,metadataPrefix',
            'possibleErrors' => 'GetRecord=>badArgument,cannotDisseminateFormat,idDoesNotExist',
        ],
        'Identify' => [
            'possibleErrors' => 'badArgument',
        ],
        'ListIdentifiers' => [
            'requiredArguments' => 'metadataPrefix',
            'possibleErrors' => 'badArgument,badResumptionToken,cannotDisseminateFormat,noRecordsMatch,noSetHierarchy',
            'allowedArguments' => 'from,until,metadataPrefix,set,resumptionToken',
        ],
        'ListMetadataFormats' => [
            'allowedArguments' => 'identifier',
            'possibleErrors' => 'badArgument,idDoesNotExist,noMetadataFormats',
        ],
        'ListRecords' => [
            'allowedArguments' => 'from,until,metadataPrefix,set,resumptionToken',
            'requiredArguments' => 'metadataPrefix',
            'possibleErrors' => 'badArgument,badResumptionToken,cannotDisseminateFormat,noRecordsMatch,noSetHierarchy',
        ],
        'ListSets' => [
            'allowedArguments' => 'resumptionToken',
            'possibleErrors' => 'badArgument,badResumptionToken,noSetHierarchy',
        ],
    ];

    private $requestAttributes = [
        'verb',
        'identifier',
        'metadataPrefix',
        'from',
        'until',
        'set',
        'resumptionToken',
    ];

    private $sets = [
        'EU' => 'Europeana',
        'dc_antiquitates.und.archaeologia' => 'Antiquitates und Archaeologia',
        'dc_antiquitates.und.archaeologia.archaeo18' => 'Antiquitates und Archaeologia - ARCHEO18',
        'dc_archeo18' => 'ARCHEO18',
        'dc_autobiographica' => 'Autobiographica',
        'dc_blumenbachiana' => 'Blumenbachiana',
        'dc_bucherhaltung' => 'Book conservation',
        'dc_digiwunschbuch' => 'DigiWunschbuch',
        'dc_göttingeruniversitätsgeschichte.gedrucktewerke' => 'Göttinger Universitätsgeschichte - Gedruckte Werke',
        'dc_itineraria' => 'Travel Literature',
        'dc_juridica' => 'History of Law',
        'dc_maps' => 'Maps',
        'dc_manuscriptae' => 'Manuscriptae',
        'dc_mathematica' => 'Mathematical Literature',
        'dc_nordamericana' => 'North American Literature',
        'dc_rezensionszeitschriften' => 'Reviews',
        'dc_rusdml' => 'RusDML',
        'dc_sibirica' => 'Sibirica',
        'dc_varia' => 'Varia',
        'dc_vd17.mainstream' => 'VD17 Mainstream',
        'dc_vd17.nova' => 'VD17-nova',
        'dc_vd18.digital' => 'VD18 digital',
        'dc_wissenschaftsgeschichte' => 'History of the Humanities and the Sciences',
        'dc_zoologica' => 'Zoologica',
    ];

    private $metadataFormats = [
        'oai_dc' => 'dc:title, dc:creator, dc:subject, dc:description, dc:publisher, dc:contributor, dc:date, dc:type, dc:format, dc:identifier, dc:source, dc:language, dc:relation, dc:coverage, dc:rights',
        'mets' => 'mets:mets',
    ];

    private $maxRecords = [
        'oai_dc:GetRecord' => 1,
        'oai_dc:ListRecords' => 500,
        'oai_dc:ListIdentifiers' => 500,
        'mets:ListRecords' => 1,
        'mets:GetRecord' => 1,
        'mets:ListIdentifiers' => 500,
];
    private $queryParameters = [
       'oai_dc' => '(ismets:1 OR docstrct:article OR docstrct:review OR docstrct:periodicalissue OR docstrct:map OR docstrct:contained_work OR docstrct:verse OR docstrct:musical_notation OR docstrct:letter) ',
       'mets' => '(ismets:1)',
    ];

    private $oaiIdentifier = [
       'xmlns' => 'http://www.openarchives.org/OAI/2.0/oai-identifier',
       'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
       'xsi:schemaLocation' => 'http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd',
       'scheme' => 'oai',
       'repositoryIdentifier' => 'gdz.sub.uni-goettingen.de',
       'delimiter' => ':',
       'sampleIdentifier' => 'oai:gdz.sub.uni-goettingen.de:PPN123456789',
    ];

    private $metadataFormatOptions = [
        'oai_dc' => [
            'schema' => 'http://www.openarchives.org/OAI/2.0/dc.xsd',
            'metadataNamespace' => 'http://purl.org/dc/elements/1.1/',
            'default' => [
                'dc:publisher' => 'Göttinger Digitalisierungszentrum',
                'dc:type' => 'Text',
            ],
            'dc' => [
                'xmlns:oai_dc' => 'http://www.openarchives.org/OAI/2.0/oai_dc/',
                'xmlns:dc' => 'http://purl.org/dc/elements/1.1/',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            ],
            'identifier' => [
                'section' => 'section',
                 'annotation' => 'annotation',
                'address' => 'address',
                'article' => 'article',
                'issue' => 'issue',
                'bachelor_thesis' => 'bachelor thesis',
                'volume' => 'volume',
                'contained_work' => 'contained work',
                'additional' => 'additional',
                'report' => 'report',
                'provenance' => 'provenance',
                'collation' => 'collation',
                'ornament' => 'ornament',
                'letter' => 'letter',
                'cover' => 'cover',
                'diploma_thesis' => 'diploma thesis',
                'doctoral_thesis' => 'doctoral thesis',
                'printers_mark' => 'printers mark',
                'binding' => 'binding',
                'entry' => 'entry',
                'corrigenda' => 'corrigenda',
                'bookplate' => 'bookplate',
                'fascicle' => 'fascicle',
                'research_paper' => 'research paper',
                'fragment' => 'fragment',
                'habilitation_thesis' => 'habilitation thesis',
                'manuscript' => 'manuscript',
                'illustration' => 'illustration',
                'imprint' => 'imprint',
                'contents' => 'table of contents',
                'initial_decoration' => 'initial decoration',
                'year' => 'year',
                'chapter' => 'chapter',
                'map' => 'map',
                'colophon' => 'colophon',
                'engraved_titlepage' => 'engraved titlepage',
                'magister_thesis' => 'magister thesis',
                'master_thesis' => 'master thesis',
                'multivolume_work' => 'multivolume work',
                'month' => 'month',
                'monograph' => 'monograph',
                'musical_notation' => 'musical notation',
                'periodical' => 'periodical',
                'privileges' => 'privileges',
                'index' => 'index',
                'spine' => 'spine',
                'scheme' => 'scheme',
                'edge' => 'edge',
                'paste_down' => 'paste down',
                'stamp' => 'stamp',
                'study' => 'study',
                'table' => 'table',
                'day' => 'day',
                'proceeding' => 'proceeding',
                'text' => 'text',
                'title_page' => 'title page',
                'verse' => 'verse',
                'preprint' => 'preprint',
                'lecture' => 'lecture',
                'endsheet' => 'endsheet',
                'paper' => 'paper',
                'preface' => 'preface',
                'dedication' => 'dedication',
                'newspaper' => 'newspaper',
        ],
            ],
        'mets' => [
          'schema' => 'http://www.loc.gov/mets/mets.xsd http://www.loc.gov/standards/mods/v3/mods-3-2.xsd',
            'metadataNamespace' => 'http://www.loc.gov/METS/ http://www.loc.gov/mods/v3',
        ],
    ];

    private $identifyTags = [
        'repositoryName' => 'GDZ - OAI Frontend',
        'baseURL' => 'http://gdz.sub.uni-goettingen.de/oai2/',
        'protocolVersion' => '2.0',
        'adminEmail' => 'gdz@sub.uni-goettingen.de',
        'deletedRecord' => 'no',
        'granularity' => 'YYYY-MM-DDTHH:MM:SSZ',
    ];

    /**
     * OaiService constructor.
     *
     * @param RequestStack $requestStack
     * @param Client       $client
     * @param string       $kernelRootDir
     * @param string       $oaiTempDirectory
     */
    public function __construct(RequestStack $requestStack, Client $client, $kernelRootDir, $oaiTempDirectory)
    {
        $this->kernelRootDir = $kernelRootDir;
        $this->requestStack = $requestStack;
        $this->client = $client;
        $this->oaiTempDirectory = $oaiTempDirectory;
    }

    public function start()
    {
        // create XML-DOM
        $this->oai = new \DOMDocument('1.0', 'UTF-8');

        //nice output format (linebreaks and tabs)
        $this->oai->formatOutput = false;

        //insert xsl
        $this->oai->appendChild($this->oai->createProcessingInstruction('xml-stylesheet',
            'href="/xsl/oai2.xsl" type="text/xsl"'));

        //Create the root-element
        $this->createRootElement();

        $arrArgs = $this->parseArguments();

        //if isset arrArgs['start'] no more checks!
        if (!isset($arrArgs['start']) && isset($arrArgs['verb'])) {
            if (isset($this->metadataFormatOptions[$arrArgs['verb']]['requiredArguments'])) {
                $this->errorRequiredArguments($arrArgs['verb'], $arrArgs);
            }
            $this->errorAllowedArguments($arrArgs['verb'], $arrArgs);
        }

        if (!isset($arrArgs['from']) && isset($arrArgs['until'])) {
            if (isset($this->metadataFormatOptions[$arrArgs['verb']]['requiredArguments'])) {
                $this->errorRequiredArguments($arrArgs['verb'], $arrArgs);
            }
            $this->errorAllowedArguments($arrArgs['verb'], $arrArgs);
        }

        if ($arrArgs['verb'] == 'Identify') {
            $this->getIdentification();
        }

        if ($arrArgs['verb'] == 'ListMetadataFormats') {
            $arrArgs = $this->getListMetadataFormats($arrArgs);
        }

        if ($arrArgs['verb'] == 'ListSets') {
            $this->listSets();
        }

        //#######################################################################################
        //#### GetRecord ########################################################################
        //#######################################################################################
        if ($arrArgs['verb'] == 'GetRecord') {
            //error handling
           $this->errorMetaDataPrefix($arrArgs);

            $this->getRecords($arrArgs, $arrResult);

            if (!$arrResult['hits']) {
                throw new OaiException('No Records Match', 1478853666);
            }

            $this->ListRecords = $this->oai->createElement($arrArgs['verb']);
            $this->oai_pmh->appendChild($this->ListRecords);
            foreach ($arrResult['header'] as $key => $val) {
                $this->record = $this->oai->createElement('record');
                $this->ListRecords->appendChild($this->record);
                $this->head = $this->oai->createElement('header');
                $this->record->appendChild($this->head);
                foreach ($val as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $_v) {
                            $this->node = $this->oai->createElement($k);
                            $this->node->appendChild(new \DOMText($_v));
                            $this->head->appendChild($this->node);
                        }
                    } else {
                        $this->node = $this->oai->createElement($k);
                        $this->node->appendChild(new \DOMText($v));
                        $this->head->appendChild($this->node);
                    }
                }
                $this->metadata = $this->oai->createElement('metadata');
                $this->record->appendChild($this->metadata);

                switch ($arrArgs['metadataPrefix']) {
                    case 'oai_dc':
                        $this->oai_dc = $this->oai->createElement('oai_dc:dc');
                        foreach ($this->metadataFormatOptions['oai_dc']['dc'] as $attribute => $value) {
                            $this->oai_dc->setAttribute($attribute, $value);
                        }
                        $this->metadata->appendChild($this->oai_dc);

                        foreach ($arrResult['metadata'][$key] as $k => $v) {
                            if ($v) {
                                foreach ($v as $_v) {
                                    if ($_v) {
                                        if ($k == 'dc:description') {
                                            $data = $this->oai->createCDATASection($_v);
                                        } else {
                                            $data = new \DOMText((string) $_v);
                                        }
                                        $this->node = $this->oai->createElement($k);
                                        $this->node->appendChild($data);
                                        $this->oai_dc->appendChild($this->node);
                                    }
                                }
                            }
                        }
                        break;
                    case 'mets':
                        foreach ($arrResult['metadata'][$key] as $k => $v) {
                            $tmp = new \DOMDocument();
                            $test = $tmp->loadXML($v);
                            if ($test) {
                                $mets = $tmp->getElementsByTagName('mets')->item(0);
                                $import = $this->oai->importNode($mets, true);
                                $this->metadata->appendChild($import);
                            }
                        }
                        break;
                }
            }

            //getRecord
        }
        //#######################################################################################
        //#### END GetRecord ####################################################################
        //#######################################################################################
        //#######################################################################################
        //#### ListRecords / LstIdentifiers #####################################################
        //#######################################################################################
        if ($arrArgs['verb'] == 'ListRecords' || $arrArgs['verb'] == 'ListIdentifiers') {
            //error handling
            $this->errorDate($arrArgs);
            $this->errorMetaDataPrefix($arrArgs);
            $this->errorFromUntil($arrArgs);
            $this->getRecords($arrArgs, $arrResult);
            if (!$arrResult['hits']) {
                throw new OaiException('No Records Match', 1478853689);
            }

            $this->ListRecords = $this->oai->createElement($arrArgs['verb']);
            $this->oai_pmh->appendChild($this->ListRecords);
            foreach ($arrResult['header'] as $key => $val) {
                if ($arrArgs['verb'] == 'ListRecords') {
                    $this->record = $this->oai->createElement('record');
                    $this->ListRecords->appendChild($this->record);
                    $this->head = $this->oai->createElement('header');
                    $this->record->appendChild($this->head);
                } else {
                    $this->head = $this->oai->createElement('header');
                    $this->ListRecords->appendChild($this->head);
                }
                foreach ($val as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $_v) {
                            $this->node = $this->oai->createElement($k);
                            $this->node->appendChild(new \DOMText($_v));
                            $this->head->appendChild($this->node);
                        }
                    } else {
                        $this->node = $this->oai->createElement($k);
                        $this->node->appendChild(new \DOMText($v));
                        $this->head->appendChild($this->node);
                    }
                }
                if ($arrArgs['verb'] == 'ListRecords') {
                    $this->metadata = $this->oai->createElement('metadata');
                    $this->record->appendChild($this->metadata);
                    switch ($arrArgs['metadataPrefix']) {
                        case 'oai_dc':
                            $this->oai_dc = $this->oai->createElement('oai_dc:dc');
                            foreach ($this->metadataFormatOptions['oai_dc']['dc'] as $attribute => $value) {
                                $this->oai_dc->setAttribute($attribute, $value);
                            }
                            $this->metadata->appendChild($this->oai_dc);
                            foreach ($arrResult['metadata'][$key] as $k => $v) {
                                if ($v) {
                                    foreach ($v as $_v) {
                                        if ($_v) {
                                            if ($k == 'dc:description') {
                                                $data = $this->oai->createCDATASection($_v);
                                            } else {
                                                $data = new \DOMText((string) $_v);
                                            }
                                            $this->node = $this->oai->createElement($k);
                                            $this->node->appendChild($data);
                                            $this->oai_dc->appendChild($this->node);
                                        }
                                    }
                                }
                            }
                            break;
                        case 'mets':
                            foreach ($arrResult['metadata'][$key] as $k => $v) {
                                $tmp = new \DOMDocument();
                                $test = $tmp->loadXML($v);
                                if ($test) {
                                    $mets = $tmp->getElementsByTagName('mets')->item(0);
                                    $import = $this->oai->importNode($mets, true);
                                    $this->metadata->appendChild($import);
                                }
                            }
                            break;
                    }
                }
            }

            $this->getResumptionToken($arrResult, $arrArgs);
        }
        //#######################################################################################
        //#### END ListRecords ##################################################################
        //#######################################################################################
    }

    /**
     * @param array $arr
     *
     * @throws OaiException
     */
    private function errorDate(&$arr)
    {
        //Array $regs :
        //[0] => YYYY-MM-DDTHH:MM:SSZ
        //[1] => YYYY
        //[2] => MM
        //[3] => DD
        //[4] => THH:MM:SSZ
        //[5] => T
        //[6] => HH
        //[7] => MM
        //[8] => SS
        //[9] => Z
        $arrDates = array('from' => '00:00:00', 'until' => '23:59:59');
        foreach ($arrDates as $key => $val) {
            if (isset($arr[$key])) {
                preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})(([T]{1})([0-9]{2}):([0-9]{2}):([0-9]{2})([Z]{1}){1})?/', $arr[$key], $regs);
                if ($regs[1] != '' && $regs[4] != '') {
                    $arr['DB'.$key] = $regs[1].'-'.$regs[2].'-'.$regs[3].' '.$regs[6].':'.$regs[7].':'.$regs[8];
                } else {
                    if ($regs[1] != '' && strlen($arr[$key]) == 10) {
                        $arr['DB'.$key] = $regs[1].'-'.$regs[2].'-'.$regs[3].' '.$val;
                    } else {
                        throw new OaiException(sprintf('Bad argument. %s: %s', $key, $arr[$key]), 1478853737);
                    }
                }
            }
        }
    }

    /**
     * @param array $arr
     *
     * @throws OaiException
     */
    private function errorFromUntil(&$arr)
    {
        if (isset($arr['from']) && isset($arr['until'])) {
            if ((strlen($arr['from'])) != (strlen($arr['until']))) {
                throw new OaiException(sprintf('Bad argument. from: %s until %s', $arr['from'], $arr['until']), 1478852818);
            } else {
                if (($arr['from']) > ($arr['until'])) {
                    throw new OaiException(sprintf('Bad argument. from: %s until %s', $arr['from'], $arr['until']), 1478852845);
                }
            }
        } else {
            if (isset($arr['from'])) {
                if (strlen($arr['from']) > strlen($this->identifyTags['granularity'])) {
                    throw new OaiException(sprintf('Bad argument. from: %s until %s', $arr['from'], $arr['until']), 1478852868);
                }
            } else {
                if (isset($arr['until'])) {
                    if (strlen($arr['until']) > strlen($this->identifyTags['granularity'])) {
                        throw new OaiException(sprintf('Bad argument. until %s', $arr['until']), 1478852906);
                    }
                }
            }
        }
    }

    private function errorMetaDataPrefix(&$arr)
    {
        if (isset($arr['metadataPrefix'])) {
            if (!array_key_exists($arr['metadataPrefix'], $this->metadataFormats)) {
                throw new OaiException(sprintf('Bad argument. metadataPrefix %s', $arr['metadataPrefix']), 1478852962);
            }
        } else {
            throw new OaiException(sprintf('Bad argument. metadataPrefix %s', ''), 1478853001);
        }
    }

    /**
     * @return array
     *
     * @throws OaiException
     */
    protected function parseArguments()
    {
        $arrArgs = $this->requestStack->getMasterRequest()->query->all();
        $arrErr = [];
        unset($arrArgs['id']);

        //prepare answer
        if (is_array($arrArgs)) {
            foreach ($arrArgs as $key => $val) {
                if ($key == 'from' || $key == 'until') {
                    if (strlen($val) != 10) {
                        continue;
                    }
                    $test = date_parse($val);
                    if (!$test || count($test['errors'])) {
                        continue;
                    }
                }
                if (array_key_exists($key, $this->requestAttributes)) {
                    if ($key == 'verb' && !array_key_exists($val, $this->verbs)) {
                        continue;
                    }
                    $this->request->setAttribute($key, $val);
                }
                if (array_key_exists($val, $this->verbs)) {
                    $this->request->setAttribute($key, $val);
                }
            }
        }
        $this->oai_pmh->appendChild($this->request);

        //same argument
        if ($this->requestStack->getMasterRequest()->getMethod() === 'GET') {
            $strQuery = $this->requestStack->getMasterRequest()->getQueryString() ?: '';
        } else {
            if ($this->requestStack->getMasterRequest()->getMethod() === 'POST') {
                $strQuery = file_get_contents('php://input');
            } else {
                $strQuery = '';
            }
        }

        $arrTmp = explode('&', $strQuery);
        if (isset($arrTmp) && count($arrTmp) > 1) {
            if (count($arrTmp) != count($GLOBALS['_'.$_SERVER['REQUEST_METHOD']])) {
                foreach ($GLOBALS['_'.$_SERVER['REQUEST_METHOD']] as $key => $val) {
                    $arrKey = array_search($key.'='.$val, $arrTmp);
                    if ($arrKey !== false) {
                        unset($arrTmp[$arrKey]);
                    }
                }
                foreach ($arrTmp as $val) {
                    $_arrTmp = explode('=', $val);
                    $arrErr[$_arrTmp[0]] = $_arrTmp[1];
                    throw new OaiException(sprintf('Bad argument %s', $arrErr), 1478853319);
                }
            }
        }

        //No verb
        if (count($arrArgs) == 0 || !isset($arrArgs['verb'])) {
            throw new OaiException(sprintf('Bad verb NOVERB: %s', ''), 1478853352);
        }

        //resumptionToken is an exclusive argument, so get all necessary args from token
        //or stop all other action
        if (is_array($arrArgs) && isset($arrArgs['resumptionToken'])) {
            if ((count($arrArgs) == 2 && !isset($arrArgs['verb'])) || count($arrArgs) > 2) {
                $arrTmp = $arrArgs;
                unset($arrTmp['resumptionToken']);
                throw new OaiException(sprintf('Bad argument %s', $arrTmp), 1478853579);
            }
            $this->restoreArgs($arrArgs);
        }
        if (isset($arrArgs['verb'])) {
            if (!array_key_exists($arrArgs['verb'], $this->verbs)) {
                throw new OaiException(sprintf('Bad verb %s: $s', $arrArgs['verb'], ''), 1478853608);
            }

            return $arrArgs;
        }

        return $arrArgs;
    }

    private function errorAllowedArguments($verb, $arr)
    {
        foreach ($arr as $key => $val) {
            if ($key != 'verb' && !@in_array($key, explode(',', $this->verbs[$verb]['allowedArguments']))) {
                throw new OaiException(sprintf('Bad argument: %: %', $key, $val), 1478853155);
            }
        }
    }

    private function errorRequiredArguments($verb, $arr)
    {
        $arrTmp = explode(',', $this->conf[$verb]['requiredArguments']);
        unset($arr['verb']);
        foreach ($arrTmp as $key => $val) {
            if (isset($arr[$val])) {
                unset($arrTmp[$key]);
                reset($arrTmp);
            }
        }
        if (count($arrTmp)) {
            $noerror = false;
            foreach ($arrTmp as $val) {
                throw new OaiException(sprintf('Bad argument: %: %', $val, ''), 1478853229);
            }
        } else {
            $noerror = true;
        }

        return $noerror;
    }

    /**
     * @param $arr
     *
     * @return bool
     *
     * @throws OaiException
     */
    private function restoreArgs(&$arr)
    {
        $strToken = @file_get_contents($this->oaiTempDirectory.$arr['resumptionToken']);
        if ($strToken != '') {
            parse_str($strToken, $arrToken);
            $arr = array_merge($arr, $arrToken);
            unset($arr['resumptionToken']);

            return true;
        } else {
            throw new OaiException(sprintf('Bad Resumption Token %s.', $arr['resumptionToken']), 1478853790);
        }
    }

    /**
     * @param string $identifier
     *
     * @return array|bool
     *
     * @throws OaiException
     */
    private function getMetadataFormats($identifier)
    {
        $arrFormats = [];
        $arrArgs['verb'] = 'GetRecord';
        $arrArgs['identifier'] = $identifier;
        foreach ($this->metadataFormats as $key => $val) {
            $arrArgs['metadataPrefix'] = $key;
            $arrResult = [];
            $this->getRecords($arrArgs, $arrResult);
            if ($arrResult['hits']) {
                array_push($arrFormats, $key);
            }
        }
        if (!count($arrFormats)) {
            throw new OaiException(sprintf('Id %s does not exist. Bad argument identifier: %s', $identifier, $identifier), 1478853862);
        }

        return $arrFormats;
    }

    /**
     * @param array $arr
     * @param array $arrResult
     *
     * @throws OaiException
     */
    private function getRecords(&$arr, &$arrResult)
    {
        $arrResult = [];
        $direction = true;
        $arr['maxresults'] = $this->maxRecords[$arr['metadataPrefix'].':'.$arr['verb']];

        if (!isset($arr['start'])) {
            $arr['start'] = 0;
        } else {
            $arr['start'] = $arr['start'] + $arr['maxresults'];
        }
        $arrResult['header'] = [];
        $arrResult['records'] = [];
        if (isset($arr['identifier'])) {
            $identifier = str_replace(
                $this->oaiIdentifier['scheme'].$this->oaiIdentifier['delimiter'].$this->oaiIdentifier['repositoryIdentifier'].$this->oaiIdentifier['delimiter'],
                '',
                trim($arr['identifier'])
            );
            $addWhere = ' (pid:"'.$identifier.'")';
        } else {
            $addWhere = '';
            if (isset($arr['from']) || isset($arr['until'])) {
                if (!isset($arr['from'])) {
                    $from = '1970-01-01T00:00:00Z';
                    $direction = true;
                } else {
                    $from = $arr['from'];
                    $direction = false;
                }
                if (!isset($arr['until'])) {
                    $until = '9999-12-31T00:00:00Z';
                } else {
                    $until = $arr['until'];
                    $direction = false;
                }
                $addWhere .= ' (dateindexed:['.$from.' TO '.$until.'])';
            }
            if (isset($arr['set'])) {
                if (isset($this->conf['setqueries'][trim($arr['set'])])) {
                    $addWhere .= ' '.$this->conf['setqueries'][trim($arr['set'])];
                } else {
                    $arrTmp = explode('_', trim($arr['set']));
                    for ($i = 1; $i < count($arrTmp); $i = $i + 2) {
                        $addWhere .= ' ('.$arrTmp[$i - 1].':'.$arrTmp[$i].')';
                    }
                    unset($arrTmp);
                }
            }
        }
        if ($this->hideCollections) {
            $addWhere .= '';
            foreach ($this->hiddenCollections as $dc) {
                $addWhere .= ' NOT(dc:'.$dc.')';
            }
            $addWhere .= '';
        }
        if ($arr['metadataPrefix']) {
            $mPrefix = $arr['metadataPrefix'];
        } else {
            $mPrefix = 'oai_dc';
        }

        $res = $this->query($this->queryParameters[$mPrefix].$addWhere, 'dateindexed', $direction, $arr);
        $arrResult['hits'] = $this->num_rows($res);
        if (!$arrResult['hits']) {
            if ($arr['verb'] == 'GetRecord') {
                throw new OaiException(sprintf('Id %s does not exist. Bad argument: identifier: %s', $identifier, $identifier), 1478853965);
            }
        }
        for ($i = 0; $i < min($arrResult['hits'], $arr['maxresults']); ++$i) {
            if (!($arrData = $res)) {
                break;
            }

            $arrResult['header'][$i]['identifier'] = $this->oaiIdentifier['scheme'].$this->oaiIdentifier['delimiter'].$this->oaiIdentifier['repositoryIdentifier'].$this->oaiIdentifier['delimiter'].trim($arrData[0][0]['pid']);
            if (!trim($arrData[0][0]['dateindexed'])) {
                $arrResult['header'][$i]['datestamp'] = $this->conf['MAIN']['datestamp'];
            } else {
                $arrResult['header'][$i]['datestamp'] = $arrData[0][0]['dateindexed'];
            }
            foreach ($arrData[0][0]['dc'] as $setSpec) {
                $setSpec = trim($setSpec);
                if ($setSpec) {
                    if ($this->sets['dc_'.$setSpec]) {
                        $arrResult['header'][$i]['setSpec'][] = 'dc_'.$setSpec;
                        if (array_key_exists('acl', $arrData) && in_array('free', $arrData['acl'])) {
                            $arrResult['header'][$i]['setSpec'][] = 'dc_'.$setSpec.'_acl_free';
                        } else {
                            if (array_key_exists('acl', $arrData) && in_array('Gesamtabo', $arrData['acl'])) {
                                $arrResult['header'][$i]['setSpec'][] = 'dc_'.$setSpec.'_acl_gesamtabo';
                            }
                        }
                    }
                }
            }
            if (isset($arr['set']) && !in_array($arr['set'], $arrResult['header'][$i]['setSpec'])) {
                array_unshift($arrResult['header'][$i]['setSpec'], $arr['set']);
            }
            $arrResult['header'][$i]['setSpec'] = array_unique($arrResult['header'][$i]['setSpec']);
            if ($arr['verb'] == 'ListRecords' || $arr['verb'] == 'GetRecord') {
                switch ($arr['metadataPrefix']) {
                    case 'oai_dc':
                        if (array_key_exists('idparentdoc', $arrData) && $arrData['idparentdoc']) {
                            $arrResult['metadata'][$i]['dc:relation'][0] = $this->getRelation($arrData);
                        }
                        $arrResult['metadata'][$i]['dc:title'][0] = $arrData[0][0]['title'][0].' '.$arrData[0][0]['currentno'];
                        $arrResult['metadata'][$i]['dc:creator'] = $arrData[0][0]['creator'];
                        $arrResult['metadata'][$i]['dc:subject'] = $arrData[0][0]['dc'];
                        $arrResult['metadata'][$i]['dc:subject'][] = $arrData[0][0]['docstrct'];
                        $arrResult['metadata'][$i]['dc:language'] = $arrData[0][0]['lang'];
                        $arrResult['metadata'][$i]['dc:publisher'] = $arrData[0][0]['publisher'];
                        $arrResult['metadata'][$i]['dc:date'][0] = $arrData[0][0]['yearpublish'];
                        $arrResult['metadata'][$i]['dc:type'][0] = $this->metadataFormatOptions['oai_dc']['identifier'][$arrData[0][0]['docstrct']];
                        $arrResult['metadata'][$i]['dc:type'][1] = $this->metadataFormatOptions['oai_dc']['default']['dc:type'];
                        $arrResult['metadata'][$i]['dc:format'][0] = 'image/jpeg';
                        $arrResult['metadata'][$i]['dc:format'][1] = 'application/pdf';
                        $arrResult['metadata'][$i]['dc:identifier'][0] = 'http://resolver.sub.uni-goettingen.de/purl?'.trim($arrData[0][0]['pid']);
                        foreach ($this->metadataFormatOptions['oai_dc']['identifier'] as $key => $val) {
                            if ($arrData[0][0][$key]) {
                                array_push($arrResult['metadata'][$i]['dc:identifier'],
                                    trim($val).' '.trim($arrData[0][0][$key]));
                            }
                        }
                        foreach ($arrData[0][0] as $key => $val) {
                            if (substr($key, 0, 3) != 'id_') {
                                continue;
                            }
                            $arrTmp = explode('_', $key);
                            array_pop($arrTmp);
                            array_shift($arrTmp);
                            $_key = strtoupper(implode('_', $arrTmp));
                            if (trim($val[0]) && isset($this->conf['oai_dc:identifier'][trim($_key)])) {
                                array_push($arrResult['metadata'][$i]['dc:identifier'],
                                    trim($this->conf['oai_dc:identifier'][$_key]).' '.trim($val[0]));
                            }
                        }
                        //Zeitschriftenband
                        // dc:source Publisher: Titel. Ort Erscheinungsjahr.
                        if (count($arrData[0][0]['structrun']) == 2) {
                            $arrResult['metadata'][$i]['dc:source'][0] = trim(trim(implode('; ',
                                    $arrData['publisher'])).': '.trim($arrData['title'][0]).'. '.trim(implode('; ',
                                    $arrData['placepublish'])).' '.trim($arrData['yearpublish']));
                        } else {
                            if (count($arrData[0][0]['structrun']) > 2) {
                                // dc:source Autor: Zeitschrift. Band Erscheinungsjahr.
                                $arrResult['metadata'][$i]['dc:source'][0] = trim(trim(implode('; ',
                                        $arrData['creator'])).': '.trim($arrData['structrun'][1]['title']).'. '.trim($arrData['structrun'][1]['currentno']).' '.trim($arrData['structrun'][1]['yearpublish']));
                            }
                        }
                        if ($arrData[0][0]['acl']) {
                            foreach ($arrData['acl'] as $val) {
                                if ($this->conf['oai_dc:rights'][$val]) {
                                    $arrResult['metadata'][$i]['dc:rights'][] = trim($this->conf['oai_dc:rights'][$val]);
                                } else {
                                    $arrResult['metadata'][$i]['dc:rights'][] = trim($val);
                                }
                            }
                        }
                        break;
                    case 'mets':
                        $arrResult['metadata'][$i]['mets:mets'] = file_get_contents('http://gdz.sub.uni-goettingen.de/mets_export.php?PPN='.trim($arrData[0][0]['pid']));
                        break;
                }
            }
        }
        //new ResumtionToken ?
        if ($arr['verb'] == 'ListRecords' || $arr['verb'] == 'ListIdentifiers') {
            if (($arrResult['hits'] - $arr['start']) > $arr['maxresults']) {
                $arrResult['token'] = 'oai_'.md5(uniqid((string) rand(), true));
                $strToken = '';
                //allowed keys
                $arrAllowed = array('from', 'until', 'metadataPrefix', 'set', 'resumptionToken', 'start');
                foreach ($arr as $key => $val) {
                    if (in_array($key, $arrAllowed)) {
                        $strToken .= $key.'='.$val.'&';
                    }
                }
                $strToken .= 'hits='.$arrResult['hits'];
                $fp = fopen($this->oaiTempDirectory.$arrResult['token'], 'w');
                fwrite($fp, $strToken);
                fclose($fp);
            } else {
                unset($arrResult['token']);
            }
        }

        return;
    }

    /**
     * @return string
     */
    private function getDatestamp()
    {
        $res = $this->query('dateindexed:{1970-01-01T00:00:00Z TO 9999-01-01T00:00:00Z}', 'dateindexed', false, ['maxresults' => 1]);

        return $res[0][0]['dateindexed'];
    }

    private function getRelation(&$arr)
    {
        $res = $this->query('iddoc:'.$arr['idparentdoc'][0], array('rows' => 1));
        $strReturn = '';
        if ($this->num_rows($res)) {
            $arrRes = $this->fetch_assoc($res);
            if (trim($arrRes['creator'])) {
                $strReturn .= trim(implode('; ', $arrRes['creator'])).': ';
            }
            if (trim($arrRes['title'])) {
                $strReturn .= trim($arrRes['title'][0]).'. ';
            }
            if (trim($arrRes['placepublish'])) {
                $strReturn .= trim(implode('; ', $arrRes['placepublish'])).' ';
            }
            if (trim($arrRes['yearpublish'])) {
                $strReturn .= trim($arrRes['yearpublish']);
            }
            if (!trim($arr['creator'])) {
                $arr['creator'] = $arrRes['creator'];
            }
            if (!trim($arr['title'])) {
                $arr['title'] = $arrRes['title'];
            }
            if (!trim($arr['placepublish'])) {
                $arr['placepublish'] = $arrRes['placepublish'];
            }
            if (!trim($arr['yearpublish'])) {
                $arr['yearpublish'] = $arrRes['yearpublish'];
            }
        }

        return trim($strReturn);
    }

    private function num_rows($res)
    {
        return count($res);
    }

    /**
     * @param string $query
     * @param string $sort
     * @param bool   $reverse
     * @param array  $configuration
     *
     * @return array
     */
    private function query($query, $sort = 'dateindexed', $reverse = false, $configuration = [])
    {
        $rows = isset($configuration['maxresults']) ? $configuration['maxresults'] : 10;
        $start = isset($configuration['start']) ? $configuration['start'] : 0;
        $direction = $reverse ? 'desc' : 'asc';

        $solrQuery = $this->client
            ->createSelect()
            ->addSort($sort, $direction)
            ->setStart($start)
            ->setRows($rows)
            ->setQuery($query);

        $arrSolr = $this->client
            ->select($solrQuery)
            ->getDocuments();

        $documents = [];
        foreach ($arrSolr as $document) {
            foreach ($document as $key => $val) {
                $documentS = $arrSolr;
                $arrTmp = explode(',', 'structrun');
                foreach ($arrTmp as $field) {
                    if (isset($arrSolr[$key][$field])) {
                        $documentS[$key][$field] = $this->_unserialize($arrSolr[$key][$field]);
                    }
                }
                array_push($documents, $documentS);
            }
        }

        return $documents;
    }

    private function fetch_assoc(&$iterator)
    {
        if ($arr = current($iterator)) {
            next($iterator);

            return $arr;
        } else {
            return false;
        }
    }

    /**
     * Helper function to switch from serialized fields to "jsonized" Fields in lucene index.
     *
     * @param string $str: serialized or jsonized string
     *
     * @return mixed unserialized or unjsonized
     */
    private function _unserialize($str)
    {
        $ret = json_decode($str, true);
        if (!is_array($ret)) {
            $ret = unserialize($str);
        }

        return $ret;
    }

    private function listSets()
    {
        $ListSets = $this->oai->createElement('ListSets');
        $this->oai_pmh->appendChild($ListSets);

        if (is_array($this->sets)) {
            foreach ($this->sets as $key => $val) {
                $set = $this->oai->createElement('set');
                $ListSets->appendChild($set);
                $node = $this->oai->createElement('setSpec', $key);
                $set->appendChild($node);
                $node = $this->oai->createElement('setName', $val);
                $set->appendChild($node);
            }
        } else {
            throw new OaiException('No set hierarchy', 1478854031);
        }
    }

    /**
     * @param $arrResult
     * @param $arrArgs
     */
    private function getResumptionToken($arrResult, $arrArgs)
    {
        if (isset($arrResult['token'])) {
            $resumptionToken = $this->oai->createElement('resumptionToken', $arrResult['token']);
            $resumptionToken->setAttribute('expirationDate',
                (gmdate('Y-m-d\TH:i:s\Z', (time() + self::EXPIRATION_DATE))));
            $resumptionToken->setAttribute('completeListSize', (string) $arrResult['hits']);
            $resumptionToken->setAttribute('cursor', (string) $arrArgs['start']);
            $this->ListRecords->appendChild($resumptionToken);
        } else {
            //return an empty resumptionToken?
            $resumptionToken = $this->oai->createElement('resumptionToken', '');
            $resumptionToken->setAttribute('expirationDate',
                (gmdate('Y-m-d\TH:i:s\Z', (time() + self::EXPIRATION_DATE))));
            $resumptionToken->setAttribute('completeListSize', (string) $arrResult['hits']);
            $resumptionToken->setAttribute('cursor', (string) $arrArgs['start']);
            $this->ListRecords->appendChild($resumptionToken);
        }
    }

    private function getIdentification()
    {
        $Identify = $this->oai->createElement('Identify');
        $this->oai_pmh->appendChild($Identify);
        foreach ($this->identifyTags as $key => $val) {
            $$key = $this->oai->createElement($key, $val);
            $Identify->appendChild($$key);
            //insert node before node: 'adminEmail'
            if ($key == 'adminEmail') {
                //get earliest datestamp
                $earliestDatestamp = $this->oai->createElement('earliestDatestamp', $this->getDatestamp());
                $Identify->appendChild($earliestDatestamp);
            }
        }
        if (isset($this->conf['oai-identifier'])) {
            $desc = $this->oai->createElement('description');
            $Identify->appendChild($desc);
            $oai_id = $this->oai->createElement('oai-identifier');
            if (isset($this->conf['oai-identifier']['xmlns'])) {
                $oai_id->setAttribute('xmlns', $this->oaiIdentifier['xmlns']);
                unset($this->conf['oai-identifier']['xmlns']);
            }
            if (isset($this->oaiIdentifier['xmlns:xsi'])) {
                $oai_id->setAttribute('xmlns:xsi', $this->oaiIdentifier['xmlns:xsi']);
                unset($this->oaiIdentifier['xmlns:xsi']);
            }
            if (isset($this->oaiIdentifier['xsi:schemaLocation'])) {
                $oai_id->setAttribute('xsi:schemaLocation', $this->oaiIdentifier['xsi:schemaLocation']);
                unset($this->oaiIdentifier['xsi:schemaLocation']);
            }
            $desc->appendChild($oai_id);
            foreach ($this->oaiIdentifier as $key => $val) {
                $$key = $this->oai->createElement($key, trim($val));
                $oai_id->appendChild($$key);
            }
        }
    }

    /**
     * @param $arrArgs
     *
     * @return array
     *
     * @throws OaiException
     */
    private function getListMetadataFormats($arrArgs)
    {
        if (isset($arrArgs['identifier'])) {
            $arrFormats = $this->getMetadataFormats($arrArgs['identifier']);
            if (!$arrFormats) {
                throw new OaiException('Formats not defined', 1478855075);
            }
            $ListMetadataFormats = $this->oai->createElement('ListMetadataFormats');
            $this->oai_pmh->appendChild($ListMetadataFormats);
            foreach ($arrFormats as $format) {
                $metadataFormat = $this->oai->createElement('metadataFormat');
                $ListMetadataFormats->appendChild($metadataFormat);
                $node = $this->oai->createElement('metadataPrefix', $format);
                $metadataFormat->appendChild($node);
                foreach ($this->conf[$format] as $key => $val) {
                    $$key = $this->oai->createElement($key, $val);
                    $metadataFormat->appendChild($$key);
                }
            }

            return array($arrArgs);
        } else {
            $ListMetadataFormats = $this->oai->createElement('ListMetadataFormats');
            $this->oai_pmh->appendChild($ListMetadataFormats);
            foreach ($this->metadataFormats as $format => $v) {
                $metadataFormat = $this->oai->createElement('metadataFormat');
                $ListMetadataFormats->appendChild($metadataFormat);
                $node = $this->oai->createElement('metadataPrefix', $format);
                $metadataFormat->appendChild($node);

                foreach ($this->metadataFormatOptions[$format] as $key => $val) {
                    if (!is_array($val)) {
                        $$key = $this->oai->createElement($key, $val);
                        $metadataFormat->appendChild($$key);
                    }
                }
            }

            return $arrArgs;
        }
    }

    private function createRootElement()
    {
        $this->oai_pmh = $this->oai->createElement('OAI-PMH');
        foreach ($this->oaiPma as $attribute => $value) {
            $this->oai_pmh->setAttribute($attribute, $value);
        }

        $this->oai->appendChild($this->oai_pmh);

        $responseDate = $this->oai->createElement('responseDate', gmdate('Y-m-d\TH:i:s\Z', time()));
        $this->oai_pmh->appendChild($responseDate);

        $this->request = $this->oai->createElement('request', 'http://gdz.sub.uni-goettingen.de/oai2/');
    }
}
