<?php
namespace MichielRoos\H5p\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use MichielRoos\H5p\Domain\Model\Content;
use MichielRoos\H5p\Domain\Model\ContentResult;
use MichielRoos\H5p\Domain\Repository\ContentRepository;
use MichielRoos\H5p\Domain\Repository\ContentResultRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class AjaxController
 */
class AjaxController extends ActionController
{
    protected ContentRepository $contentRepository;
    protected ContentResultRepository $contentResultRepository;
    protected FrontendUserRepository $frontendUserRepository;
    protected PersistenceManager $persistenceManager;

    /**
     * @var string
     */
    private $language;

    public function injectContentRepository(ContentRepository $contentRepository): void
    {
        $this->contentRepository = $contentRepository;
    }

    public function injectContentResultRepository(ContentResultRepository $contentResultRepository): void
    {
        $this->contentResultRepository = $contentResultRepository;
    }

    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository): void
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Finish action
     */
    public function finishAction(): ResponseInterface
    {
        $user = null;

        $error = [
            'message'    => 'Unable to save result',
            'errorCode'  => 'error',
            'statusCode' => 200,
            'details'    => 'No user is logged in'
        ];

        $context = GeneralUtility::makeInstance(Context::class);
        if ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn')) {
            $request = $this->request;
            $frontendUser = $request->getAttribute('frontend.user');
            $user = $frontendUser->user;
            $postData = $request->getParsedBody();
            if (!array_key_exists('time', $postData)) {
                $postData['time'] = 0;
            }

            $content = $this->contentRepository->findByUid($postData['contentId']);
            if (!$content instanceof Content) {
                $error['details'] = 'Content not found';
                \H5PCore::ajaxError($error['message'], $error['errorCode'], $error['statusCode'], $error['details']);
                return $this->jsonResponse(json_encode($error));
            }

            $frontendUserModel = $this->frontendUserRepository->findByUid((int)$user['uid']);

            /** @var ContentResult $existingContentResult */
            $existingContentResult = $this->contentResultRepository->findOneByUserAndContentId($user['uid'], $postData['contentId']);
            if ($existingContentResult) {
                $existingContentResult->setScore($postData['score']);
                $existingContentResult->setMaxScore($postData['maxScore']);
                $existingContentResult->setOpened($postData['opened']);
                $existingContentResult->setFinished($postData['finished']);
                $existingContentResult->setTime($postData['time']);
                $this->contentResultRepository->update($existingContentResult);
            } else {
                $pageId = (int)($request->getAttribute('routing')?->getPageId() ?? 0);
                $contentResult = new ContentResult($content, $frontendUserModel, (int)$postData['score'], (int)$postData['maxScore'], (int)$postData['opened'], (int)$postData['finished'], (int)$postData['time']);
                $contentResult->setPid($pageId);
                $this->contentResultRepository->add($contentResult);
            }
            $this->persistenceManager->persistAll();
            \H5PCore::ajaxSuccess();
            return $this->jsonResponse(json_encode(['success' => true]));
        }
        \H5PCore::ajaxError($error['message'], $error['errorCode'], $error['statusCode'], $error['details']);
        return $this->jsonResponse(json_encode($error));
    }

    /**
     * Content user data action
     */
    public function contentUserDataAction(): ResponseInterface
    {
        return $this->jsonResponse(json_encode([]));
    }

    /**
     * Returns an instance of LanguageService
     *
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
