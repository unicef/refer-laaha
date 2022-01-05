<?php

namespace Drupal\visitors\Controller\Report;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Top pages.
 */
class TopPages extends ControllerBase {
  /**
   * The date service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $date;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('form_builder')
    );
  }

  /**
   * Constructs a TopPages object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(DateFormatterInterface $date_formatter, FormBuilderInterface $form_builder) {
    $this->date        = $date_formatter;
    $this->formBuilder = $form_builder;
  }

  /**
   * Returns a top pages page.
   *
   * @return array
   *   A render array representing the top pages page content.
   */
  public function display() {
    $form = $this->formBuilder->getForm('Drupal\visitors\Form\DateFilter');
    $header = $this->getHeader();

    return [
      'visitors_date_filter_form' => $form,
      'visitors_table' => [
        '#type'  => 'table',
        '#header' => $header,
        '#rows'   => $this->getData($header),
      ],
      'visitors_pager' => ['#type' => 'pager'],
    ];
  }

  /**
   * Returns a table header configuration.
   *
   * @return array
   *   A render array representing the table header info.
   */
  protected function getHeader() {
    return [
      '#' => [
        'data'      => t('#'),
      ],
      'visitors_url' => [
        'data'      => t('Content title (This will include all content pages, Category, Sub category page, Homepage, FAQ, About Us)'),
        'field'     => 'visitors_title',
        'specifier' => 'visitors_title',
        'class'     => [RESPONSIVE_PRIORITY_LOW],
      ],
      'count' => [
        'data'      => t('URL hit times'),
        'field'     => 'count',
        'specifier' => 'count',
        'class'     => [RESPONSIVE_PRIORITY_LOW],
        // 'sort'      => 'desc',
      ],
    ];
  }

  /**
   * Returns a table content.
   *
   * @param array $header
   *   Table header configuration.
   *
   * @return array
   *   Array representing the table content.
   */
  protected function getData(array $header) {
    // $items_per_page = \Drupal::config('visitors.config')->get('items_per_page');
    $items_per_page = 20;

    $query = \Drupal::database()->select('visitors', 'v')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('Drupal\Core\Database\Query\TableSortExtender');

    $query->addExpression('COUNT(visitors_id)', 'count');
    $query->addExpression('MIN(visitors_title)', 'visitors_title');
    $query->addExpression('MIN(visitors_url)', 'visitors_url');
    $query->fields('v', ['visitors_path']);
    visitors_date_filter_sql_condition($query);
    $query->groupBy('visitors_path');
    $query->orderByHeader($header);
    $query->limit($items_per_page);

    $count_query = \Drupal::database()->select('visitors', 'v');
    $count_query->addExpression('COUNT(DISTINCT visitors_path)');
    visitors_date_filter_sql_condition($count_query);
    $query->setCountQuery($count_query);
    $results = $query->execute();

    $rows = [];
    $get_page = \Drupal::request()->query->get('page');
    $page = isset($get_page) ? $get_page : 0;
    $i = 0 + $page * $items_per_page;
    // @todo add links
    foreach ($results as $data) {
      $rows[] = [
        ++$i,
        $data->visitors_title,
        $data->count,
      ];
    }

    return $rows;
  }

}
