import { useState, useEffect } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	useSettings,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import {
	PanelBody,
	TextControl,
	Button,
	Spinner,
	SelectControl,
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Link Card block editor component.
 *
 * Allows pasting a URL to fetch and preview Open Graph metadata,
 * and renders the card preview in the editor.
 * @param {Object}   root0
 * @param {Object}   root0.attributes
 * @param {Function} root0.setAttributes
 */
export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		url,
		title,
		description,
		image,
		domain,
		imageWidth,
		imageHeight,
		imageAspectRatio,
		imageObjectFit,
		cardMaxWidth,
	} = attributes;
	const [ aspectRatios ] = useSettings( 'dimensions.aspectRatios' );
	const blockClassName = useSelect(
		( select ) =>
			select( 'core/block-editor' ).getBlockAttributes( clientId )
				?.className ?? '',
		[ clientId ]
	);
	const isCompactStacked = blockClassName.includes( 'is-style-compact-stacked' );
	const isFixedAspectRatioStyle =
		blockClassName.includes( 'is-style-compact' ) || isCompactStacked;
	const [ inputUrl, setInputUrl ] = useState( url );
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( null );

	const blockProps = useBlockProps( {
		className: 'bb-link-card',
		style: isCompactStacked ? { maxWidth: cardMaxWidth } : undefined,
	} );

	useEffect( () => {
		if ( url && ! title ) {
			fetchPreview( url );
		}
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	async function fetchPreview( fetchUrl ) {
		if ( ! fetchUrl ) {
			return;
		}
		setLoading( true );
		setError( null );
		try {
			const data = await apiFetch( {
				path: `/better-bookmarks/v1/preview?url=${ encodeURIComponent(
					fetchUrl
				) }`,
			} );
			setAttributes( {
				url: data.url,
				title: data.title,
				description: data.description,
				image: data.image,
				domain: data.domain,
				imageWidth: data.imageWidth ?? 0,
				imageHeight: data.imageHeight ?? 0,
			} );
			setInputUrl( data.url );
		} catch ( err ) {
			setError(
				err.message ??
					__( 'Could not fetch preview.', 'better-bookmarks' )
			);
		} finally {
			setLoading( false );
		}
	}

	function handleKeyDown( e ) {
		if ( e.key === 'Enter' ) {
			e.preventDefault();
			fetchPreview( inputUrl );
		}
	}

	const hasCard = url && title;

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Link', 'better-bookmarks' ) }>
					<TextControl
						label={ __( 'URL', 'better-bookmarks' ) }
						value={ inputUrl }
						onChange={ setInputUrl }
						onKeyDown={ handleKeyDown }
						placeholder="https://"
						type="url"
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<Button
						variant="secondary"
						onClick={ () => fetchPreview( inputUrl ) }
						disabled={ loading || ! inputUrl }
					>
						{ loading ? (
							<Spinner />
						) : (
							__( 'Fetch Preview', 'better-bookmarks' )
						) }
					</Button>
					{ error && <p className="bb-editor-error">{ error }</p> }
				</PanelBody>
			</InspectorControls>

			{ isCompactStacked && (
				<InspectorControls group="styles">
					<PanelBody title={ __( 'Layout', 'better-bookmarks' ) }>
						<UnitControl
							label={ __( 'Max width', 'better-bookmarks' ) }
							value={ cardMaxWidth }
							units={ [
								{ value: 'px', label: 'px', default: 320 },
								{ value: 'em', label: 'em', default: 20 },
								{ value: 'rem', label: 'rem', default: 20 },
								{ value: '%', label: '%', default: 100 },
							] }
							onChange={ ( val ) =>
								setAttributes( { cardMaxWidth: val } )
							}
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>
					</PanelBody>
				</InspectorControls>
			) }

			{ image && (
				<InspectorControls group="styles">
					<PanelBody title={ __( 'Image', 'better-bookmarks' ) }>
						{ ! isFixedAspectRatioStyle && (
							<SelectControl
								label={ __(
									'Aspect ratio',
									'better-bookmarks'
								) }
								value={ imageAspectRatio }
								__next40pxDefaultSize
								options={ [
									{
										label: __(
											'Original',
											'better-bookmarks'
										),
										value: '',
									},
									...( aspectRatios ?? [] )
										.filter( ( r ) => r.ratio !== 'auto' )
										.map( ( r ) => ( {
											label: r.name,
											value: r.ratio,
										} ) ),
								] }
								onChange={ ( val ) =>
									setAttributes( { imageAspectRatio: val } )
								}
							/>
						) }
						<SelectControl
							label={ __( 'Image fit', 'better-bookmarks' ) }
							value={ imageObjectFit }
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							options={ [
								{
									label: __( 'Contain', 'better-bookmarks' ),
									value: 'contain',
								},
								{
									label: __( 'Cover', 'better-bookmarks' ),
									value: 'cover',
								},
							] }
							onChange={ ( val ) =>
								setAttributes( { imageObjectFit: val } )
							}
						/>
					</PanelBody>
				</InspectorControls>
			) }

			{ ! hasCard && (
				<div className="bb-link-card-editor__placeholder">
					<input
						type="url"
						className="bb-link-card-editor__url-input"
						value={ inputUrl }
						onChange={ ( e ) => setInputUrl( e.target.value ) }
						onKeyDown={ handleKeyDown }
						placeholder={ __(
							'Paste a URL and press Enter…',
							'better-bookmarks'
						) }
					/>
					{ loading && <Spinner /> }
					{ error && <p className="bb-editor-error">{ error }</p> }
				</div>
			) }

			{ hasCard && (
				<div className="bb-link-card__link">
					{ image && (
						<div
							className="bb-link-card__image-wrap"
							style={ {
								aspectRatio:
									imageAspectRatio ||
									( imageWidth && imageHeight
										? `${ imageWidth } / ${ imageHeight }`
										: '1.91 / 1' ),
							} }
						>
							<img
								className="bb-link-card__image"
								src={ image }
								alt=""
								style={ { objectFit: imageObjectFit } }
							/>
						</div>
					) }
					<div className="bb-link-card__body">
						{ domain && (
							<span className="bb-link-card__domain">
								{ domain }
							</span>
						) }
						{ title && (
							<strong className="bb-link-card__title">
								{ title }
							</strong>
						) }
						{ description && (
							<p className="bb-link-card__description">
								{ description }
							</p>
						) }
					</div>
				</div>
			) }
		</div>
	);
}
