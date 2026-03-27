<?php
/**
 * Server-side render for better-bookmarks/link-card.
 *
 * @package BetterBookmarks
 *
 * @var array  $attributes Block attributes.
 * @var string $content    Inner block content (unused).
 * @var object $block      The WP_Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$better_bookmarks_url                = $attributes['url'] ?? '';
$better_bookmarks_title              = $attributes['title'] ?? '';
$better_bookmarks_description        = $attributes['description'] ?? '';
$better_bookmarks_image              = $attributes['image'] ?? '';
$better_bookmarks_domain             = $attributes['domain'] ?? '';
$better_bookmarks_image_width        = (int) ( $attributes['imageWidth'] ?? 0 );
$better_bookmarks_image_height       = (int) ( $attributes['imageHeight'] ?? 0 );
$better_bookmarks_image_aspect_ratio = $attributes['imageAspectRatio'] ?? '';
$better_bookmarks_aspect_ratio_style = 'aspect-ratio:' . esc_attr(
	$better_bookmarks_image_aspect_ratio
		? $better_bookmarks_image_aspect_ratio
		: ( $better_bookmarks_image_width && $better_bookmarks_image_height ? $better_bookmarks_image_width . ' / ' . $better_bookmarks_image_height : '1.91 / 1' )
);

if ( ! $better_bookmarks_url ) {
	return;
}

$better_bookmarks_wrapper_attributes = get_block_wrapper_attributes(
	array( 'class' => 'bb-link-card' )
);
?>
<div <?php echo $better_bookmarks_wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a class="bb-link-card__link" href="<?php echo esc_url( $better_bookmarks_url ); ?>" target="_blank" rel="noopener noreferrer">
		<?php if ( $better_bookmarks_image ) : ?>
		<div class="bb-link-card__image-wrap"
			<?php
			if ( $better_bookmarks_aspect_ratio_style ) :
				?>
			style="<?php echo $better_bookmarks_aspect_ratio_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>"<?php endif; ?>>
			<img
				class="bb-link-card__image"
				src="<?php echo esc_url( $better_bookmarks_image ); ?>"
				alt=""
				loading="lazy"
			/>
		</div>
		<?php endif; ?>
		<div class="bb-link-card__body">
			<?php if ( $better_bookmarks_domain ) : ?>
			<span class="bb-link-card__domain"><?php echo esc_html( $better_bookmarks_domain ); ?></span>
			<?php endif; ?>
			<?php if ( $better_bookmarks_title ) : ?>
			<strong class="bb-link-card__title"><?php echo esc_html( $better_bookmarks_title ); ?></strong>
			<?php endif; ?>
			<?php if ( $better_bookmarks_description ) : ?>
			<p class="bb-link-card__description"><?php echo esc_html( $better_bookmarks_description ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</div>
