<?php

namespace Drupal\erpw_in_app_notification\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ReadNotificaitonEventSubscriber.
 */
class ReadNotificaitonEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Define the event you want to subscribe to.
    $events[KernelEvents::REQUEST][] = ['onKernelRequest'];
    return $events;
  }

  /**
   * Custom event callback.
   */
  public function onKernelRequest(RequestEvent $event) {
    // Update notification read status.
    $npeid = \Drupal::request()->query->get('_npeid');
    if (isset($npeid)) {
      if (preg_match('/^\d+$/', $npeid)) {
        $npeidobj = \Drupal::entityTypeManager()->getStorage('notification_processed_entity')->load($npeid);
        $npeidobj->set('field_read', 1);
        $npeidobj->save();
      }
    }
  }

}
