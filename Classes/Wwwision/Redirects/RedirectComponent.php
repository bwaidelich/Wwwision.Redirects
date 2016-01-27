<?php
namespace Wwwision\Redirects;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Component\ComponentContext;
use TYPO3\Flow\Http\Component\ComponentInterface;
use TYPO3\Flow\Mvc\Routing\RoutingComponent;
use Wwwision\Redirects\Domain\Service\RedirectService;

class RedirectComponent implements ComponentInterface
{

    /**
     * @Flow\Inject
     * @var RedirectService
     */
    protected $redirectService;

    /**
     * @param ComponentContext $componentContext
     * @return void
     */
    public function handle(ComponentContext $componentContext)
    {
        $routingMatchResults = $componentContext->getParameter(RoutingComponent::class, 'matchResults');
        if ($routingMatchResults !== NULL) {
            return;
        }
        $httpRequest = $componentContext->getHttpRequest();
        $this->redirectService->triggerRedirectIfApplicable($httpRequest);
    }
}