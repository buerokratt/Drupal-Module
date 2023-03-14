<?php

namespace Drupal\vp_boostai\EventSubscriber;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Asset\LibraryDependencyResolverInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\csp\Csp;
use Drupal\csp\CspEvents;
use Drupal\csp\Event\PolicyAlterEvent;
use Drupal\vp_boostai\Form\BoostAiConfigForm;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter CSP policy for Boost AI.
 */
class VpBoostAiCspSubscriber implements EventSubscriberInterface {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The Library Dependency Resolver service.
   *
   * @var \Drupal\Core\Asset\LibraryDependencyResolverInterface
   */
  private $libraryDependencyResolver;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   */
  public function __construct(ConfigFactory $configFactory, LibraryDependencyResolverInterface $libraryDependencyResolver) {
    $this->configFactory = $configFactory;
    $this->libraryDependencyResolver = $libraryDependencyResolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[CspEvents::POLICY_ALTER] = ['onCspPolicyAlter'];
    return $events;
  }

  /**
   * Alter CSP policy.
   *
   * @param \Drupal\csp\Event\PolicyAlterEvent $alterEvent
   *   The Policy Alter event.
   */
  public function onCspPolicyAlter(PolicyAlterEvent $alterEvent): void {
    $policy = $alterEvent->getPolicy();

    $settings = $this->configFactory->getEditable(BoostAiConfigForm::SETTINGS);

    $urls = [];

    if ($settings->get('vp_boostai_api_url')) {
      $urls[] = $settings->get('vp_boostai_api_url');
    }

    if ($settings->get('vp_boostai_js_url')) {
      $urls[] = $settings->get('vp_boostai_js_url');
    }

    if (!empty($urls)) {
      $policy->appendDirective('connect-src', $urls);
      $policy->appendDirective('script-src-elem', $urls);
    }

    $policy = $alterEvent->getPolicy();
    $response = $alterEvent->getResponse();

    if ($response instanceof AttachmentsInterface) {
      $attachments = $response->getAttachments();
      $libraries = isset($attachments['library']) ?
        $this->libraryDependencyResolver->getLibrariesWithDependencies($attachments['library']) :
        [];

      // Check if page has clndr library enabled.
      if (in_array('vp_boostai/vp_boostai.chatbot_init', $libraries, TRUE)) {
        // Add unsafe-eval csp flag to make clndr work.
        $policy->fallbackAwareAppendIfEnabled('script-src', [Csp::POLICY_UNSAFE_EVAL]);
      }
    }
  }

}
