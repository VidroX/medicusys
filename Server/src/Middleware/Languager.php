<?php
/**
 * Created by PhpStorm.
 * User: VidroX
 * Date: 4/11/2019
 * Time: 12:01 PM
 */

namespace App\Middleware;

use App\Utils\i18n;
use Slim\Http\Request;
use Slim\Http\Response;

class Languager
{
    private $i18n;

    public function __construct(i18n $i18n) {
        $this->i18n = $i18n;
    }

    /**
     * Middleware to redirect with language code
     *
     * @param  \Slim\Http\Request                       $request  PSR7 request
     * @param  \Slim\Http\Response                      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next) {
        if ($request->getMethod() == 'GET') {
            $chunks = explode("/", $request->getUri()->getPath());

            if(count($chunks) > 1) {
                $language = $chunks[1];
                if (!empty($language) && $this->i18n->isLanguageCodeAllowed($language)){
                    $this->i18n->setLanguageCode($language);
                    $tempPath = "/" . implode("/", $chunks);
                    $url = $request->getUri()->withPath($tempPath);
                    $request = $request->withUri($url);
                } else {
                    $this->i18n->setLanguageCode(i18n::getBrowserLocale());
                    $tempPath = "/" . $this->i18n->getLanguageCode() . implode("/", $chunks);
                    $url = $request->getUri()->withPath($tempPath);
                    if($this->i18n->isDefaultLanguageCodeHidden()) {
                        $request = $request->withUri($url);
                    }else{
                        return $response->withRedirect($url, 301);
                    }
                }
            }
        }

        return $next($request, $response);
    }
}