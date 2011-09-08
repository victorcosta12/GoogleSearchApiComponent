<?php

App::import('Helper', 'Html');

class GoogleSearchApiComponent extends Object {



        protected $url = 'http://ajax.googleapis.com/ajax/services/search/web?v=1.0&rsz=large&start=%s&q=%s';



        var $resultado, $pagina, $keywords;

        function __construct() {

                if (!function_exists('curl_init')) {

                        trigger_error('A biblioteca cURL não está instalada!');

                        return false;

                }

                if (!function_exists('json_decode')) {

                        trigger_error('A biblioteca para manipulação de JSON não está instalada!');

                        return false;

                }

        }

        /**

         * Pega o resultado HTTP de uma URL

         */

        protected function httpRequest($url) {

                $cURL = curl_init($url);

                curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);

                curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, true);

                $resultado = curl_exec($cURL);

                $resposta = curl_getinfo($cURL, CURLINFO_HTTP_CODE);

                curl_close($cURL);

                return $resultado;

        }

        /**

         * Executa a busca

         */

        function busca($keywords = null, $pagina = 1, $site = null) {

                $keywords = (is_null($keywords)) ? $this->keywords : $keywords;

                $start = (is_null($pagina)) ? 0 : (($pagina - 1) * 8);

                $bkeywords = (!is_null($site)) ? ($keywords . ' site:' . $site) : $keywords;

                $url = sprintf($this->url, (int)$start, urlencode($bkeywords));

                $resultado = $this->httpRequest($url);

                if (!$resultado) {

                        trigger_error('Não foi possível acessar a URL de busca:<br />' . $url);

                        return false;

                }

                $resultado = json_decode($resultado, true);

                $this->resultado = $resultado['responseData'];

                $this->keywords = $keywords;

                $this->pagina = $pagina;

        }

        /**

         * Pega os resultados encontrados

         */

        function resultadoSites() {

                return $this->resultado['results'];

        }

        /**

         * Pega o total de sites encontrados

         */

        function resultadoTotal() {

                return count($this->resultado['cursor']['pages']);

        }

        function paginacao($controller,$action,$separator=''){

            $pagina = $this->pagina;
            $keywords = $this->keywords;
            $totalPaginas = $this->resultadoTotal();

            $html = new HtmlHelper();

            $paginacao = '';

            for ($n = 1; $n <= $totalPaginas; $n++) {
                if (($n == 1)&&($pagina!=1)){
                    $paginacao.= '<span>';
                    $paginacao.= $html->link('<',array('controller'=>$controller,'action'=>$action,$keywords,$pagina-1)).'&nbsp;'.$separator.'&nbsp;';
                    $paginacao.= '</span>';
                }

                if ($n == $pagina){
                    $paginacao.= $html->tag('span',$n,array('class'=>'current')).'&nbsp;'.$separator.'&nbsp;';
                }else{
                    $paginacao.= '<span>';
                    $paginacao.= $html->link($n,array('controller'=>$controller,'action'=>$action,$keywords,$n));
                    $paginacao.= '</span>';
                }
                if (($n==$totalPaginas)&&($pagina!=$totalPaginas)){
                    $paginacao.= '<span>';
                    $paginacao.= $html->link('>',array('controller'=>$controller,'action'=>$action,$keywords,$pagina+1));
                    $paginacao.= '</span>';
                }
            }

            return $paginacao;


        }

}
?>

