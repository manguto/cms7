<?php
namespace manguto\cms7\application\core;

use manguto\cms7\libraries\ServerHelp;
use manguto\cms7\libraries\Exception;
use manguto\cms7\libraries\File;
use manguto\cms7\libraries\Logger;

class Page
{

    // diretorio em questao (front,back,dev)
    private $tpl_dir;

    // arquivo base principal
    private $tpl_filename;

    // caminho para arquivo do template
    private $tpl_path;

    // nome do arquivo
    private $tpl_basename;

    // controla a reutilizacao do cache
    private $tpl_parameters;

    // conteudo do cache (*.PHP)
    private $cache_content;

    // manter arquivos de cache ( DEV=>true|PROD=>false )
    const keep_tpl_cache = false;

    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
    public function __construct(string $tpl_dir, string $tpl_filename, array $tpl_parameters = [])
    {
        Logger::info("Construção de nova página iniciada ($tpl_dir | $tpl_filename).");
        $this->tpl_dir = $tpl_dir;
        $this->tpl_filename = ServerHelp::fixds($tpl_filename);
        $this->tpl_basename = basename($this->tpl_filename);
        $this->tpl_path = $this->getTplPath();
        $this->tpl_content = File::getContent($this->tpl_path);
        $this->tpl_parameters = $tpl_parameters;
        $this->cache_content = $this->loadCacheContent();
    }

    // ####################################################################################################
    /**
     * obtencao do codigo (PHP+HTML) do template em questao
     */
    private function loadCacheContent(): string
    {
        $PageReplacer = new PageReplacer($this->tpl_dir, $this->tpl_content, $this->tpl_parameters);
        $return = $PageReplacer->run();
        //Logger::success("Conteudo do template convertido com sucesso ({$this->tpl_basename}.php).");
        return $return;
    }

    // ####################################################################################################
    /**
     * retorna o conteudo do cache (PHP+HTML)
     *
     * @return string
     */
    public function getCacheContent(): string
    {
        return $this->cache_content;
    }

    // ####################################################################################################

    /**
     * exibe ou retorna template executado
     *
     * @param bool $toString
     * @return string|null
     */
    public function run(bool $toString = false)
    {
        //Logger::info("Execucao do template '{$this->tpl_basename}' solicitada!");

        { // executa o template sem a sua impressao

            try {

                // salvamento do arquivo de cache HTML/PHP para interpretacao
                $this->saveTplCache($this->cache_content);

                // obtencao da interpretacao do template (HTML)
                $return = $this->RunCache();

                // caso NAO tenha ocorrido nenhum problema e NAO seja necessario manter o cache, remove-o
                $this->deleteCache();

                // retorna ou imprime o template interpretado
                if ($toString == true) {
                    Logger::info("RETORNO da execução do cache solicitado.");
                    return $return;
                } else {
                    Logger::info("Impressão da execução do cache solicitado.");
                    print $return;
                }
            } catch (\Throwable $t) {

                // log do erro
                Logger::info("Mensagem de erro retornada em substituição ao conteúdo da execução do cache.");

                // retorna a mensagem de erro ao inves do template interpretado com o erro
                $return = Exception::getEventMessage($t);

                // levanta a excecao
                throw new Exception($return);
            }
        }
    }

    // ####################################################################################################

    /**
     * executa o cache
     *
     * @throws Exception
     * @return string
     */
    private function RunCache(): string
    {
        try {

            // inicia 'output buffer'
            ob_start();
            {
                // extracao dos parametros para utilizacao no template
                extract($this->tpl_parameters);
                // verificacao do arquivo do template
                File::verificarArquivoOuPastaExiste($filename = $this->getCacheFilename());
                // carregamento
                require $filename;
            }
            // obtencao do conteudo produzido
            $return = ob_get_contents();
            // finaliza o 'output buffer'
            ob_end_clean();

            { // verificacao de eventos indevidos!
                $eventsMessages = Exception::checkGetThrownEventMsg($return);
                if (sizeof($eventsMessages) > 0) {
                    // levanta a excecao para a sua exibicao e tratamento
                    throw new Exception('<h1>Exceptions/Errors/Warnings:</h1>'.implode('<br/>' . chr(10), $eventsMessages));
                }
            }

            // ================================================== SUCESSO!
            // log do sucesso
            Logger::success("Conteúdo do cache executado ({$this->tpl_basename}).");
            // retorno do conteudo interpretado
            return $return;
            // ================================================== SUCESSO!
            //
        } catch (\Throwable $t) {
            //
            // verifica e finaliza o 'output buffer' caso necessario
            $ob_status = ob_get_status();
            $level = $ob_status['level'];
            if ($level > 0) {
                ob_end_clean();
            }
            // ================================================= ERRO!
            // log do erro
            Logger::error("Conteúdo do cache NÃO executado ({$this->tpl_basename}).");
            // levantamento de excecao com a mensagem do erro encontrado
            $throwableMessage = Exception::getEventMessage($t);
            //deb($throwableMessage);
            throw new Exception($throwableMessage);
            // ================================================= ERRO!
        }
    }

    // ####################################################################################################
    /**
     * salvamento do cache
     */
    private function saveTplCache(string $content)
    {
        // save
        File::writeContent($this->getCacheFilename(), $content);
        Logger::success("Arquivo de cache salvo");
    }

    // ####################################################################################################
    /**
     * obtem o nome do arquivo (completo) do template informado
     *
     * @return string
     */
    private function getTplPath(): string
    {
        { // caminho do arquivo de template
            $return = $this->tpl_dir . $this->tpl_filename . '.html';
            $return = ServerHelp::fixds($return);
            if (! file_exists($return)) {
                throw new Exception("O template informado ('{$this->tpl_filename}') não foi encontrado no diretório solicitado ('{$this->tpl_dir}').");
            }
        }
        return $return;
    }

    // ####################################################################################################
    /**
     * obtem o nome do arquivo (completo) de cache para o template informado
     *
     * @return string
     */
    private function getCacheFilename(): string
    {
        { // parametros
            { // diretorio do cache
                $dir = APP_TPL_CACHE_DIR;
            }
            { // parametro anterior ao nome do arquivo
                $filename = $this->tpl_dir . $this->tpl_filename;
                $filename = str_replace('..', '', $filename);
                $filename = str_replace(APP_TPL_DIR, '', $filename);
                $filename = str_replace(DS, '_', $filename);
            }
            { // time
                $key = APP_TICKET_ID;
                $ip = APP_USER_IP_MASKED;
            }
            { // extensao
                $ext = '.php';
            }
        }
        $return = "{$dir}{$key}_{$ip}_{$filename}{$ext}";
        $return = ServerHelp::fixds($return);
        return $return;
    }

    // ####################################################################################################
    /**
     * realiza a exclusao do cache criado
     */
    private function deleteCache()
    {
        if (self::keep_tpl_cache == false) {
            File::delete($this->getCacheFilename());
            Logger::success("Arquivo do cache removido");
        } else {
            Logger::info("Arquivo do cache mantido");
        }
    }
    // ####################################################################################################
    // ####################################################################################################
    // ####################################################################################################
}