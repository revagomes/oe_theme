<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Drupal\oe_theme\ValueObject\ImageValueObject;
use Drupal\oe_theme\ValueObject\MediaValueObject;

/**
 * Test media content pattern rendering.
 */
class MediaContentPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test that media container pattern is correctly rendered.
   *
   * @param array $media
   *   Media data for the pattern.
   * @param array $assertions
   *   Test assertions.
   *
   * @throws \Exception
   *
   * @dataProvider dataProvider
   */
  public function testMediaPattern(array $media, array $assertions) {
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'media_container',
      '#fields' => [
        'description' => $media['description'],
        'media' => $media['media'],
      ],
    ];

    $html = $this->renderRoot($pattern);
    $this->assertRendering($html, $assertions);
  }

  /**
   * Data provider for testMediaPattern.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function dataProvider(): array {
    $data = $this->getFixtureContent('patterns/media_content_pattern.yml');
    foreach ($data as $key => $value) {
      $media = $data[$key]['media'];
      if (isset($media['media']['image'])) {
        $media['media']['image'] = ImageValueObject::fromArray($media['media']['image']);
      }
      if (isset($media['media'])) {
        $data[$key]['media']['media'] = MediaValueObject::fromArray($media['media'])->getArray();
      }
    }
    return $data;
  }

}
