import { useState } from '@wordpress/element';
import {
	useBlockProps,
	InspectorControls,
	useSettings,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Button,
	Spinner,
	SelectControl,
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
export default function Edit( { attributes, setAttributes } ) {
	const {
		url,
		title,
		description,
		image,
		domain,
		imageWidth,
		imageHeight,
		imageAspectRatio,
	} = attributes;
	const [ aspectRatios ] = useSettings( 'dimensions.aspectRatios' );
	const [ inputUrl, setInputUrl ] = useState( url );
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( null );

	const blockProps = useBlockProps( { className: 'bb-link-card' } );

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
					{ image && (
						<SelectControl
							label={ __(
								'Image aspect ratio',
								'better-bookmarks'
							) }
							value={ imageAspectRatio }
							options={ [
								{
									label: __(
										'Original (1.91:1)',
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
				</PanelBody>
			</InspectorControls>

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
