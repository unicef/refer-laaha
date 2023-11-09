<?php

namespace Drupal\erpw_in_app_notification;

/**
 * Interface Helper Service Interface.
 */
interface HelperServiceInterface {

  /**
   * Get notifiation event by event ID.
   *
   * @param string $machine_name
   *   The machine_name of the notification event.
   *
   * @return array
   *   The information of notification event.
   */
  public function getEventDetailsByEventId($machine_name);

  /**
   * Get unprocessed notifiation ids.
   *
   * @return array
   *   The information of notification list from notification entities id.
   */
  public function getNotificationIds();

  /**
   * Get times ago/date from timestamp.
   *
   * @param string $timestamp
   *   The timestamp.
   *
   * @param string $formate
   *   The time format.
   *
   * @return string
   */
  public function getDynamicDateFormate($timestamp, $formate = 'd F Y');

  /**
   * Get userid by transition,
   *
   * @return int
   *   The user id of the evaluator.
   */
  public function getEvaluatorOfUser($uid, $transition);

  /**
   * Get notification count.
   * 
   * @param int
   *  Optional uid.
   * 
   * @param string
   *  entity type, service or user.
   * 
   * @return init
   *  Count of notification.
   */
  public function getCountByUid($uid = NULL, $type = NULL);

}
