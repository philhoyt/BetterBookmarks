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

$bb_url         = $attributes['url'] ?? '';
$bb_title       = $attributes['title'] ?? '';
$bb_description = $attributes['description'] ?? '';
$bb_image       = $attributes['image'] ?? '';
$bb_domain      = $attributes['domain'] ?? '';

if ( ! $bb_url ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array( 'class' => 'bb-link-card' )
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a class="bb-link-card__link" href="<?php echo esc_url( $bb_url ); ?>" target="_blank" rel="noopener noreferrer">
		<?php if ( $bb_image ) : ?>
		<div class="bb-link-card__image-wrap">
			<img
				class="bb-link-card__image"
				src="<?php echo esc_url( $bb_image ); ?>"
				alt=""
				loading="lazy"
			/>
		</div>
		<?php endif; ?>
		<div class="bb-link-card__body">
			<?php if ( $bb_domain ) : ?>
			<span class="bb-link-card__domain"><?php echo esc_html( $bb_domain ); ?></span>
			<?php endif; ?>
			<?php if ( $bb_title ) : ?>
			<strong class="bb-link-card__title"><?php echo esc_html( $bb_title ); ?></strong>
			<?php endif; ?>
			<?php if ( $bb_description ) : ?>
			<p class="bb-link-card__description"><?php echo esc_html( $bb_description ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</div>
