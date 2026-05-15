import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import apiFetch from '@wordpress/api-fetch';
import Edit from '../edit';

// @wordpress/* packages are mapped to manual stubs in jest.config.js.
// api-fetch is a jest.fn() stub — reset between tests.
beforeEach( () => apiFetch.mockReset() );

const defaultAttributes = {
	url: '',
	title: '',
	description: '',
	image: '',
	domain: '',
	imageWidth: 0,
	imageHeight: 0,
	imageAspectRatio: '',
	imageObjectFit: 'cover',
	cardMaxWidth: '320px',
};

function renderEdit( attributes = {}, setAttributes = jest.fn() ) {
	return render(
		<Edit
			attributes={ { ...defaultAttributes, ...attributes } }
			setAttributes={ setAttributes }
			clientId="test-client-id"
		/>
	);
}

describe( 'Edit — placeholder state', () => {
	it( 'renders the URL input when no card data is present', () => {
		renderEdit();
		expect(
			screen.getByRole( 'textbox', { name: /link url/i } )
		).toBeInTheDocument();
	} );

	it( 'calls apiFetch when Enter is pressed in the URL input', async () => {
		apiFetch.mockResolvedValueOnce( {
			url: 'https://example.com',
			title: 'Example',
			description: 'A site',
			image: '',
			domain: 'example.com',
			imageWidth: 0,
			imageHeight: 0,
		} );

		renderEdit();
		const input = screen.getByRole( 'textbox', { name: /link url/i } );
		await userEvent.type( input, 'https://example.com' );
		fireEvent.keyDown( input, { key: 'Enter' } );

		await waitFor( () => expect( apiFetch ).toHaveBeenCalledTimes( 1 ) );
		expect( apiFetch ).toHaveBeenCalledWith( {
			path: expect.stringContaining( 'example.com' ),
		} );
	} );

	it( 'displays an error message when the fetch fails', async () => {
		apiFetch.mockRejectedValueOnce( { message: 'Network error' } );

		renderEdit();
		const input = screen.getByRole( 'textbox', { name: /link url/i } );
		await userEvent.type( input, 'https://bad.example' );
		fireEvent.keyDown( input, { key: 'Enter' } );

		// Error appears in both the sidebar panel and the canvas placeholder.
		const errors = await screen.findAllByText( 'Network error' );
		expect( errors.length ).toBeGreaterThan( 0 );
	} );
} );

describe( 'Edit — card preview state', () => {
	it( 'renders the card title and domain when attributes are populated', () => {
		renderEdit( {
			url: 'https://example.com',
			title: 'Example Site',
			domain: 'example.com',
		} );

		expect( screen.getByText( 'Example Site' ) ).toBeInTheDocument();
		expect( screen.getByText( 'example.com' ) ).toBeInTheDocument();
	} );

	it( 'renders the card image when an image URL is provided', () => {
		renderEdit( {
			url: 'https://example.com',
			title: 'Example Site',
			image: 'https://example.com/og.png',
			imageWidth: 1200,
			imageHeight: 630,
		} );

		// alt="" marks the image as decorative; query by class instead.
		const img = document.querySelector( '.bb-link-card__image' );
		expect( img ).toHaveAttribute( 'src', 'https://example.com/og.png' );
	} );

	it( 'does not render the placeholder input once a card is loaded', () => {
		renderEdit( {
			url: 'https://example.com',
			title: 'Example Site',
		} );

		expect(
			screen.queryByRole( 'textbox', { name: /link url/i } )
		).not.toBeInTheDocument();
	} );
} );
