/**
 * Enable username editing on profile screens.
 *
 * @package Mudrava\AdminTweaks\Modules\AdminTweaks
 */
document.addEventListener( 'DOMContentLoaded', function () {
	const usernameField = document.getElementById( 'user_login' );
	if ( ! usernameField ) {
		return;
	}

	usernameField.removeAttribute( 'disabled' );
	usernameField.removeAttribute( 'readonly' );
	usernameField.style.backgroundColor = '';

	const form = usernameField.closest( 'form' );
	if ( ! form ) {
		return;
	}

	form.addEventListener( 'submit', function () {
		usernameField.removeAttribute( 'disabled' );
		usernameField.removeAttribute( 'readonly' );
	} );
} );