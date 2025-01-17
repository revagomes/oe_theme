<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the file teaser pattern.
 */
class FileTeaserAssert extends FileTranslationAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    $assertions = parent::getAssertions($variant);
    $assertions['thumbnail'] = [
      [$this, 'assertImage'],
      'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail img.ecl-file__image',
    ];
    $assertions['teaser'] = [
      [$this, 'assertElementText'],
      'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__description',
    ];
    $assertions['meta'] = [
      [$this, 'assertMeta'],
    ];
    $assertions['lists'] = [
      [$this, 'assertLists'],
    ];
    $assertions['badge'] = [
      [$this, 'assertBadge'],
      'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__label',
    ];
    return $assertions;
  }

  /**
   * Asserts the meta of the pattern.
   *
   * @param string|null $expected_metas
   *   The expected meta items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertMeta($expected_metas, Crawler $crawler): void {
    if (is_null($expected_metas)) {
      $this->assertElementNotExists('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__detail-meta span.ecl-file__detail-meta-item', $crawler);
      return;
    }
    if (!is_array($expected_metas)) {
      $expected_metas = [$expected_metas];
    }
    $meta_items = $crawler->filter('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__detail-meta span.ecl-file__detail-meta-item');
    self::assertCount(count($expected_metas), $meta_items, 'The expected meta item number does not correspond with the found meta item number.');
    foreach ($expected_metas as $index => $expected_meta) {
      self::assertEquals($expected_meta, trim($meta_items->eq($index)->text()), \sprintf('The expected text of the meta number %s does not correspond to the found meta text.', $index));
    }
  }

  /**
   * Asserts the lists of the pattern.
   *
   * @param array|null $expected_lists
   *   The expected lists items.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertLists($expected_lists, Crawler $crawler): void {
    if (is_null($expected_lists)) {
      $this->assertElementNotExists('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__taxonomy', $crawler);
      return;
    }
    $list_terms = $crawler->filter('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__taxonomy dl.ecl-description-list--taxonomy dt.ecl-description-list__term');
    $list_definitions = $crawler->filter('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__taxonomy dl.ecl-description-list--taxonomy dd.ecl-description-list__definition');
    self::assertCount(count($expected_lists), $list_terms, 'The expected list number does not correspond with the found list number.');
    foreach ($expected_lists as $index => $expected_list) {
      foreach ($expected_list as $term => $definitions) {
        self::assertEquals($term, trim($list_terms->eq($index)->text()), \sprintf('The expected text of the term number %s does not correspond to the found term text.', $index));
        self::assertEquals(implode($definitions), trim($list_definitions->eq($index)->text()), \sprintf('The expected text of the definition number %s does not correspond to the found definition text.', $index));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function assertFile(array $expected_file, Crawler $crawler): void {
    // Assert title.
    $this->assertElementText($expected_file['title'], 'div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__detail div.ecl-file__detail-info div.ecl-file__title', $crawler);

    // Assert information.
    $file_info_element = $crawler->filter('div.ecl-file--thumbnail div.ecl-file__container div.ecl-file__info');
    $this->assertElementText($expected_file['language'], 'div.ecl-file__language', $file_info_element);
    $this->assertElementText($expected_file['meta'], 'div.ecl-file__meta', $file_info_element);

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'div.ecl-file--thumbnail div.ecl-file__container a.ecl-file__download', 'href', $crawler);
  }

  /**
   * {@inheritdoc}
   */
  protected function assertTranslation(array $expected_file, Crawler $crawler): void {
    // Assert details.
    $file_info_element = $crawler->filter('div.ecl-file__translation-detail');
    $this->assertElementText($expected_file['title'], ' div.ecl-file__translation-title', $file_info_element);
    $this->assertElementText($expected_file['description'], 'div.ecl-file__translation-description', $file_info_element);

    // Assert information.
    $file_info_element = $crawler->filter('div.ecl-file__translation-info');
    $this->assertElementText($expected_file['language'], ' div.ecl-file__translation-language', $file_info_element);
    $this->assertElementText($expected_file['meta'], 'div.ecl-file__translation-meta', $file_info_element);

    // Assert download link.
    $this->assertElementAttribute($expected_file['url'], 'a.ecl-file__translation-download', 'href', $crawler);
  }

  /**
   * Asserts the badge of the file.
   *
   * @param array|null $badge
   *   The expected badge.
   * @param string $selector
   *   The CSS selector to find the badge.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertBadge(?array $badge, string $selector, Crawler $crawler): void {
    if (is_null($badge)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $selector = $selector . ' span.ecl-label.ecl-label--' . $badge['variant'];
    self::assertStringContainsString($badge['label'], $crawler->filter($selector)->text());
  }

}
