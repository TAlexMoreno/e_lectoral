<?php

namespace App\Security;

use App\Entity\Usuario;
use App\Enums\EstadosUsuario;
use App\Repository\UsuarioRepository;
use AXS\ApiBundle\Controller\Api;
use AXS\ApiBundle\Utils\JWT;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Authenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator, 
        private EventDispatcherInterface $dispatcher, 
        private UsuarioRepository $urepo,
        private Api $api    
    ){
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        $passport = new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
        $apiToken = JWT::generate(
            ["alg" => "HS256", "typ" => "JWT"], 
            ["iss" => "web", "exp" => (new DateTime())->modify("+1 day")->getTimestamp(), "sub" => $token->getUserIdentifier()],
            $this->api->getSecret()
        );
        $request->getSession()->set("api_token", $apiToken);
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        /** @var Usuario $user*/
        $user = $token->getUser();
        if ($user->getEstatus() == EstadosUsuario::Blocked) {
            $logoutEvent = new LogoutEvent($request, $token);
            $this->dispatcher->dispatch($logoutEvent);
            return $this->onAuthenticationFailure($request, new AuthenticationException("Usuario bloqueado", "1"));
        }
        if ($user->getEstatus() == EstadosUsuario::LostAccess){
            $user->setEstatus(EstadosUsuario::Operative);
            $user->setRecuperationCode(null);
        }
        if ($user->getEstatus() == EstadosUsuario::JustCreated){
            $targetPath = "/changePass";
        }
        $user->setLastIP($request->getClientIp());
        $user->setLastAccess(new DateTime());
        $this->urepo->save($user, true);
        return new JsonResponse([
            "success" => true,
            "action" => "redirect",
            "path" => $targetPath ?? $this->urlGenerator->generate("app_index")
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response {
        return new JsonResponse([
            "success" => false,
            "error" => $exception->getMessage(),
            "errno" => $exception->getCode()
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
