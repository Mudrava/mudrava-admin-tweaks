<?php
/**
 * Reusable admin UI components.
 *
 * Every module uses these methods to render form elements, cards,
 * tabs, alerts, etc. This ensures visual consistency across the
 * entire plugin without duplicating markup.
 *
 * All output is already escaped. Methods return HTML strings.
 *
 * @package Mudrava\Kit\Core
 */

declare(strict_types=1);

namespace Mudrava\Kit\Core;

final class AdminUI {

	/**
	 * Allowed admin HTML for late escaping of helper-rendered UI.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public static function allowedHtml(): array {
		return [
			'a'        => [
				'class'         => true,
				'href'          => true,
				'target'        => true,
				'rel'           => true,
				'role'          => true,
				'aria-selected' => true,
				'aria-label'    => true,
				'data-tab'      => true,
				'id'            => true,
			],
			'button'   => [
				'type'                => true,
				'class'               => true,
				'id'                  => true,
				'data-tab'            => true,
				'data-mdkit-confirm'  => true,
				'aria-label'          => true,
			],
			'div'      => [
				'class'         => true,
				'id'            => true,
				'hidden'        => true,
				'role'          => true,
				'aria-modal'    => true,
				'data-keywords' => true,
			],
			'span'     => [
				'class'       => true,
				'id'          => true,
				'aria-hidden' => true,
			],
			'p'        => [
				'class' => true,
			],
			'h1'       => [
				'class' => true,
			],
			'h3'       => [
				'class' => true,
			],
			'nav'      => [
				'class' => true,
				'role'  => true,
			],
			'label'    => [
				'class' => true,
				'for'   => true,
			],
			'input'    => [
				'type'         => true,
				'id'           => true,
				'name'         => true,
				'value'        => true,
				'class'        => true,
				'checked'      => true,
				'min'          => true,
				'max'          => true,
				'step'         => true,
				'placeholder'  => true,
				'autocomplete' => true,
			],
			'textarea' => [
				'id'    => true,
				'name'  => true,
				'rows'  => true,
				'class' => true,
			],
			'select'   => [
				'id'    => true,
				'name'  => true,
				'class' => true,
			],
			'option'   => [
				'value'    => true,
				'selected' => true,
			],
			'img'      => [
				'src'    => true,
				'alt'    => true,
				'class'  => true,
				'id'     => true,
				'width'  => true,
				'height' => true,
			],
			'table'    => [
				'class' => true,
			],
			'tbody'    => [],
			'thead'    => [],
			'tr'       => [
				'class' => true,
			],
			'th'       => [
				'class' => true,
				'scope' => true,
			],
			'td'       => [
				'class'   => true,
				'colspan' => true,
			],
			'ul'       => [
				'class' => true,
			],
			'li'       => [
				'class' => true,
			],
			'svg'      => [
				'class'            => true,
				'xmlns'            => true,
				'width'            => true,
				'height'           => true,
				'viewbox'          => true,
				'viewBox'          => true,
				'fill'             => true,
				'stroke'           => true,
				'stroke-width'     => true,
				'stroke-linecap'   => true,
				'stroke-linejoin'  => true,
				'aria-hidden'      => true,
			],
			'path'     => [
				'd'                => true,
				'fill'             => true,
				'stroke'           => true,
				'stroke-width'     => true,
				'stroke-linecap'   => true,
				'stroke-linejoin'  => true,
			],
			'circle'   => [
				'cx'            => true,
				'cy'            => true,
				'r'             => true,
				'fill'          => true,
				'stroke'        => true,
				'stroke-width'  => true,
			],
			'line'     => [
				'x1'               => true,
				'x2'               => true,
				'y1'               => true,
				'y2'               => true,
				'stroke'           => true,
				'stroke-width'     => true,
				'stroke-linecap'   => true,
				'stroke-linejoin'  => true,
			],
			'polyline' => [
				'points'           => true,
				'fill'             => true,
				'stroke'           => true,
				'stroke-width'     => true,
				'stroke-linecap'   => true,
				'stroke-linejoin'  => true,
			],
			'polygon'  => [
				'points'           => true,
				'fill'             => true,
				'stroke'           => true,
				'stroke-width'     => true,
				'stroke-linecap'   => true,
				'stroke-linejoin'  => true,
			],
			'rect'     => [
				'x'             => true,
				'y'             => true,
				'width'         => true,
				'height'        => true,
				'rx'            => true,
				'ry'            => true,
				'fill'          => true,
				'stroke'        => true,
				'stroke-width'  => true,
			],
			'ellipse'  => [
				'cx'            => true,
				'cy'            => true,
				'rx'            => true,
				'ry'            => true,
				'fill'          => true,
				'stroke'        => true,
				'stroke-width'  => true,
			],
		];
	}

	/* ------------------------------------------------------------------
	 * Layout
	 * ----------------------------------------------------------------*/

	/**
	 * Page wrapper — opens the standard page container.
	 *
	 * @param string               $title       Page title.
	 * @param string               $description Optional subtitle / description.
	 * @param array<string,string> $tabs        Tabs as slug => label.
	 * @param string               $activeTab   Currently active tab slug.
	 * @param string               $icon        Lucide icon name for the header.
	 */
	public static function pageHeader(
		string $title,
		string $description = '',
		array $tabs = [],
		string $activeTab = '',
		string $icon = '',
		bool $showBrandLogo = false,
	): string {
		$html = '<div class="wrap mdkit-wrap">';
		$html .= '<div class="mdkit-page-header">';
		$html .= '<div class="mdkit-page-header__title-row">';

		if ( $icon !== '' ) {
			$html .= '<span class="mdkit-page-header__icon">' . Icons::render( $icon, 28 ) . '</span>';
		}

		$html .= '<h1 class="mdkit-page-header__title">';
		if ( $showBrandLogo ) {
			$html .= '<a class="mdkit-page-header__brand" href="https://mudrava.com/en/" target="_blank" rel="noopener">';
			$html .= '<svg class="mdkit-page-header__brand-logo" width="140" height="28" viewBox="0 0 497 100" fill="none" xmlns="http://www.w3.org/2000/svg">' .
				'<path d="M497 100H0V0H497V100Z" fill="#021D69"/>' .
				'<path d="M17.24 20.832V17.952H34.904L62.552 76.704L59.384 84H46.904L17.24 20.832ZM71.48 56.256H71.096L64.664 71.808L56.408 54.144L71.48 17.952H89.72V84H71.48V56.256ZM17.24 30.336L34.904 67.968V84H17.24V30.336Z" fill="white"/>' .
				'<path d="M129.057 84.768C122.337 84.768 117.153 84.224 113.505 83.136C109.857 82.048 107.169 80.288 105.441 77.856C103.841 75.616 102.849 72.768 102.465 69.312C102.081 65.856 101.889 60.704 101.889 53.856V17.952H121.089V57.696C121.089 60.064 121.153 62.336 121.281 64.512C121.409 66.24 121.697 67.488 122.145 68.256C122.593 69.024 123.361 69.504 124.449 69.696C125.409 69.952 126.945 70.08 129.057 70.08H131.266C131.777 70.08 132.354 70.016 132.993 69.888V84.672C132.546 84.736 131.905 84.768 131.073 84.768H129.057ZM137.025 17.952H156.225V53.856C156.225 60.128 156.097 64.864 155.841 68.064C155.585 71.264 154.881 73.952 153.729 76.128C152.449 78.624 150.497 80.544 147.873 81.888C145.249 83.232 141.633 84.096 137.025 84.48V17.952Z" fill="white"/>' .
				'<path d="M168.459 17.952H187.659V84H168.459V17.952ZM191.691 69.312H192.459C195.595 69.312 197.803 69.184 199.083 68.928C200.427 68.608 201.419 67.904 202.059 66.816C202.763 65.664 203.147 63.84 203.211 61.344C203.339 58.144 203.403 54.688 203.403 50.976C203.403 47.328 203.339 43.84 203.211 40.512C203.083 38.016 202.667 36.192 201.963 35.04C201.323 33.888 200.267 33.184 198.795 32.928C197.323 32.736 195.211 32.64 192.459 32.64H191.691V17.952H192.459C197.579 17.952 201.835 18.176 205.227 18.624C208.683 19.072 211.531 19.776 213.771 20.736C215.947 21.696 217.675 23.008 218.955 24.672C220.235 26.336 221.163 28.416 221.739 30.912C222.251 33.152 222.571 35.808 222.699 38.88C222.891 41.888 222.987 45.92 222.987 50.976C222.987 56.096 222.891 60.16 222.699 63.168C222.571 66.176 222.251 68.8 221.739 71.04C221.163 73.536 220.235 75.616 218.955 77.28C217.675 78.944 215.947 80.256 213.771 81.216C211.531 82.176 208.683 82.88 205.227 83.328C201.835 83.776 197.579 84 192.459 84H191.691V69.312Z" fill="white"/>' .
				'<path d="M233.709 17.952H252.909V84H233.709V17.952ZM260.781 61.536H256.941V46.848H260.205C262.189 46.848 263.693 46.784 264.717 46.656C265.741 46.464 266.541 46.144 267.117 45.696C267.629 45.248 267.981 44.576 268.173 43.68C268.365 42.784 268.461 41.472 268.461 39.744C268.461 38.016 268.365 36.704 268.173 35.808C267.981 34.848 267.629 34.144 267.117 33.696C266.605 33.248 265.837 32.96 264.813 32.832C263.853 32.704 262.317 32.64 260.205 32.64H256.941V17.952H266.829C271.373 17.952 275.053 18.4 277.869 19.296C280.685 20.192 282.861 21.568 284.397 23.424C285.805 25.152 286.733 27.296 287.181 29.856C287.693 32.416 287.949 35.712 287.949 39.744C287.949 44.928 287.469 48.928 286.509 51.744C285.165 55.328 282.797 57.856 279.405 59.328L289.389 84H269.229L260.781 61.536Z" fill="white"/>' .
				'<path d="M313.372 17.952H315.004L322.588 44.256L311.548 84H292.348L313.372 17.952ZM334.972 72.288H318.94L322.972 57.504H330.652L319.324 17.952H336.892L357.916 84H338.332L334.972 72.288Z" fill="white"/>' .
				'<path d="M373.419 81.216C372.587 78.656 371.851 76.16 371.211 73.728L368.139 63.168C367.115 59.712 366.315 57.12 365.739 55.392C365.227 53.472 364.811 52.032 364.491 51.072L354.699 17.952H374.283L392.235 84H374.283L373.419 81.216ZM388.491 54.72L398.187 17.952H417.387L407.595 51.072L404.043 63.168C402.891 66.88 401.835 70.4 400.875 73.728C400.235 76.16 399.499 78.656 398.667 81.216L397.803 84H396.459L388.491 54.72Z" fill="white"/>' .
				'<path d="M435.247 17.952H436.879L444.463 44.256L433.423 84H414.223L435.247 17.952ZM456.847 72.288H440.815L444.847 57.504H452.527L441.199 17.952H458.767L479.791 84H460.207L456.847 72.288Z" fill="white"/>' .
				'</svg>';
			$html .= '</a>';
			$html .= ' Kit';
		} else {
			$html .= esc_html( $title );
		}
		$html .= '</h1>';
		$html .= '</div>';

		if ( $description !== '' ) {
			$html .= '<p class="mdkit-page-header__desc">' . esc_html( $description ) . '</p>';
		}

		if ( $tabs !== [] ) {
			$html .= self::tabs( $tabs, $activeTab );
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Close the page wrapper opened by pageHeader().
	 */
	public static function pageFooter(): string {
		$year = gmdate( 'Y' );
		$logo = '<svg class="mdkit-footer__logo" width="120" height="24" viewBox="0 0 497 100" fill="none" xmlns="http://www.w3.org/2000/svg">' .
			'<path d="M497 100H0V0H497V100Z" fill="#021D69"/>' .
			'<path d="M17.24 20.832V17.952H34.904L62.552 76.704L59.384 84H46.904L17.24 20.832ZM71.48 56.256H71.096L64.664 71.808L56.408 54.144L71.48 17.952H89.72V84H71.48V56.256ZM17.24 30.336L34.904 67.968V84H17.24V30.336Z" fill="white"/>' .
			'<path d="M129.057 84.768C122.337 84.768 117.153 84.224 113.505 83.136C109.857 82.048 107.169 80.288 105.441 77.856C103.841 75.616 102.849 72.768 102.465 69.312C102.081 65.856 101.889 60.704 101.889 53.856V17.952H121.089V57.696C121.089 60.064 121.153 62.336 121.281 64.512C121.409 66.24 121.697 67.488 122.145 68.256C122.593 69.024 123.361 69.504 124.449 69.696C125.409 69.952 126.945 70.08 129.057 70.08H131.266C131.777 70.08 132.354 70.016 132.993 69.888V84.672C132.546 84.736 131.905 84.768 131.073 84.768H129.057ZM137.025 17.952H156.225V53.856C156.225 60.128 156.097 64.864 155.841 68.064C155.585 71.264 154.881 73.952 153.729 76.128C152.449 78.624 150.497 80.544 147.873 81.888C145.249 83.232 141.633 84.096 137.025 84.48V17.952Z" fill="white"/>' .
			'<path d="M168.459 17.952H187.659V84H168.459V17.952ZM191.691 69.312H192.459C195.595 69.312 197.803 69.184 199.083 68.928C200.427 68.608 201.419 67.904 202.059 66.816C202.763 65.664 203.147 63.84 203.211 61.344C203.339 58.144 203.403 54.688 203.403 50.976C203.403 47.328 203.339 43.84 203.211 40.512C203.083 38.016 202.667 36.192 201.963 35.04C201.323 33.888 200.267 33.184 198.795 32.928C197.323 32.736 195.211 32.64 192.459 32.64H191.691V17.952H192.459C197.579 17.952 201.835 18.176 205.227 18.624C208.683 19.072 211.531 19.776 213.771 20.736C215.947 21.696 217.675 23.008 218.955 24.672C220.235 26.336 221.163 28.416 221.739 30.912C222.251 33.152 222.571 35.808 222.699 38.88C222.891 41.888 222.987 45.92 222.987 50.976C222.987 56.096 222.891 60.16 222.699 63.168C222.571 66.176 222.251 68.8 221.739 71.04C221.163 73.536 220.235 75.616 218.955 77.28C217.675 78.944 215.947 80.256 213.771 81.216C211.531 82.176 208.683 82.88 205.227 83.328C201.835 83.776 197.579 84 192.459 84H191.691V69.312Z" fill="white"/>' .
			'<path d="M233.709 17.952H252.909V84H233.709V17.952ZM260.781 61.536H256.941V46.848H260.205C262.189 46.848 263.693 46.784 264.717 46.656C265.741 46.464 266.541 46.144 267.117 45.696C267.629 45.248 267.981 44.576 268.173 43.68C268.365 42.784 268.461 41.472 268.461 39.744C268.461 38.016 268.365 36.704 268.173 35.808C267.981 34.848 267.629 34.144 267.117 33.696C266.605 33.248 265.837 32.96 264.813 32.832C263.853 32.704 262.317 32.64 260.205 32.64H256.941V17.952H266.829C271.373 17.952 275.053 18.4 277.869 19.296C280.685 20.192 282.861 21.568 284.397 23.424C285.805 25.152 286.733 27.296 287.181 29.856C287.693 32.416 287.949 35.712 287.949 39.744C287.949 44.928 287.469 48.928 286.509 51.744C285.165 55.328 282.797 57.856 279.405 59.328L289.389 84H269.229L260.781 61.536Z" fill="white"/>' .
			'<path d="M313.372 17.952H315.004L322.588 44.256L311.548 84H292.348L313.372 17.952ZM334.972 72.288H318.94L322.972 57.504H330.652L319.324 17.952H336.892L357.916 84H338.332L334.972 72.288Z" fill="white"/>' .
			'<path d="M373.419 81.216C372.587 78.656 371.851 76.16 371.211 73.728L368.139 63.168C367.115 59.712 366.315 57.12 365.739 55.392C365.227 53.472 364.811 52.032 364.491 51.072L354.699 17.952H374.283L392.235 84H374.283L373.419 81.216ZM388.491 54.72L398.187 17.952H417.387L407.595 51.072L404.043 63.168C402.891 66.88 401.835 70.4 400.875 73.728C400.235 76.16 399.499 78.656 398.667 81.216L397.803 84H396.459L388.491 54.72Z" fill="white"/>' .
			'<path d="M435.247 17.952H436.879L444.463 44.256L433.423 84H414.223L435.247 17.952ZM456.847 72.288H440.815L444.847 57.504H452.527L441.199 17.952H458.767L479.791 84H460.207L456.847 72.288Z" fill="white"/>' .
			'</svg>';

		$html  = '<div class="mdkit-footer">';
		$html .= '<div class="mdkit-footer__brand">';
		$html .= '<a href="https://mudrava.com/en/" target="_blank" rel="noopener">' . $logo . '</a>';
		$html .= '</div>';
		$html .= '<div class="mdkit-footer__info">';
		$html .= '<span class="mdkit-footer__copy">&copy; ' . esc_html( $year ) . ' MUDRAVA. All rights reserved.</span>';
		$html .= '<span class="mdkit-footer__sep">&middot;</span>';
		$html .= '<a class="mdkit-footer__link" href="https://mudrava.com/en/" target="_blank" rel="noopener">mudrava.com</a>';
		$html .= '<span class="mdkit-footer__sep">&middot;</span>';
		$html .= '<a class="mdkit-footer__link" href="mailto:support@mudrava.com">support@mudrava.com</a>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '</div><!-- .mdkit-wrap -->';

		return $html;
	}

	/* ------------------------------------------------------------------
	 * Tabs
	 * ----------------------------------------------------------------*/

	/**
	 * Horizontal tab navigation.
	 *
	 * Uses data-tab attributes for JS-powered in-place content switching.
	 * Pair with `.mdkit-tab-panels` container holding `.mdkit-tab-panel` divs
	 * whose IDs follow the pattern `mdkit-panel-{slug}`.
	 *
	 * @param array<string,string> $tabs      Slug => label.
	 * @param string               $activeTab Active tab slug.
	 */
	public static function tabs( array $tabs, string $activeTab = '' ): string {
		$html = '<nav class="mdkit-tabs" role="tablist">';

		$first = true;
		foreach ( $tabs as $slug => $label ) {
			$isActive = $activeTab !== '' ? ( $slug === $activeTab ) : $first;
			$class    = 'mdkit-tabs__item' . ( $isActive ? ' mdkit-tabs__item--active' : '' );

			$html .= sprintf(
				'<a href="#" class="%s" role="tab" aria-selected="%s" data-tab="%s">%s</a>',
				esc_attr( $class ),
				$isActive ? 'true' : 'false',
				esc_attr( $slug ),
				esc_html( $label ),
			);

			$first = false;
		}

		$html .= '</nav>';

		return $html;
	}

	/* ------------------------------------------------------------------
	 * Cards
	 * ----------------------------------------------------------------*/

	/**
	 * Content card.
	 *
	 * @param string $title   Card title.
	 * @param string $body    Inner HTML (already escaped by caller).
	 * @param string $icon    Optional Lucide icon name.
	 * @param string $footer  Optional footer HTML.
	 * @param string $class   Additional CSS class.
	 */
	public static function card(
		string $title,
		string $body,
		string $icon = '',
		string $footer = '',
		string $class = '',
	): string {
		$cssClass = 'mdkit-card' . ( $class !== '' ? ' ' . esc_attr( $class ) : '' );

		$html = '<div class="' . $cssClass . '">';

		if ( $title !== '' || $icon !== '' ) {
			$html .= '<div class="mdkit-card__header">';

			if ( $icon !== '' ) {
				$html .= '<span class="mdkit-card__icon">' . Icons::render( $icon, 20 ) . '</span>';
			}

			if ( $title !== '' ) {
				$html .= '<h3 class="mdkit-card__title">' . esc_html( $title ) . '</h3>';
			}

			$html .= '</div>';
		}

		$html .= '<div class="mdkit-card__body">' . $body . '</div>';

		if ( $footer !== '' ) {
			$html .= '<div class="mdkit-card__footer">' . $footer . '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/* ------------------------------------------------------------------
	 * Buttons
	 * ----------------------------------------------------------------*/

	/**
	 * Button.
	 *
	 * @param string               $label   Button text.
	 * @param string               $type    'primary' | 'secondary' | 'danger' | 'link'.
	 * @param array<string,string> $attrs   Extra HTML attributes (e.g. 'href', 'data-*', 'type').
	 * @param string               $icon    Optional Lucide icon name (rendered before label).
	 */
	public static function button(
		string $label,
		string $type = 'secondary',
		array $attrs = [],
		string $icon = '',
	): string {
		$class = 'mdkit-btn mdkit-btn--' . esc_attr( $type );

		if ( isset( $attrs['class'] ) ) {
			$class .= ' ' . esc_attr( $attrs['class'] );
			unset( $attrs['class'] );
		}

		$tag       = isset( $attrs['href'] ) ? 'a' : 'button';
		$attrParts = [ 'class="' . $class . '"' ];

		if ( $tag === 'button' && ! isset( $attrs['type'] ) ) {
			$attrParts[] = 'type="button"';
		}

		foreach ( $attrs as $k => $v ) {
			$attrParts[] = esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
		}

		$inner = '';
		if ( $icon !== '' ) {
			$inner .= '<span class="mdkit-btn__icon">' . Icons::render( $icon, 16 ) . '</span>';
		}
		$inner .= '<span class="mdkit-btn__label">' . esc_html( $label ) . '</span>';

		return sprintf( '<%s %s>%s</%s>', $tag, implode( ' ', $attrParts ), $inner, $tag );
	}

	/* ------------------------------------------------------------------
	 * Form elements
	 * ----------------------------------------------------------------*/

	/**
	 * Toggle switch (on/off).
	 */
	public static function toggle( string $name, bool $checked, string $label = '' ): string {
		$id = 'mdkit-toggle-' . esc_attr( $name );

		$html  = '<label class="mdkit-toggle" for="' . $id . '">';
		$html .= '<input type="checkbox" id="' . $id . '" name="' . esc_attr( $name ) . '" value="1"'
				. ( $checked ? ' checked' : '' ) . ' class="mdkit-toggle__input">';
		$html .= '<span class="mdkit-toggle__slider"></span>';

		if ( $label !== '' ) {
			$html .= '<span class="mdkit-toggle__label">' . esc_html( $label ) . '</span>';
		}

		$html .= '</label>';

		return $html;
	}

	/**
	 * Text / password / number / email input.
	 *
	 * @param string               $name        Input name.
	 * @param string               $value       Current value.
	 * @param string               $type        Input type.
	 * @param string               $label       Label text.
	 * @param string               $description Help text below the input.
	 * @param array<string,string> $attrs       Extra HTML attributes.
	 */
	public static function input(
		string $name,
		string $value = '',
		string $type = 'text',
		string $label = '',
		string $description = '',
		array $attrs = [],
	): string {
		// Allow custom id via $attrs, otherwise generate a default.
		$id = isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : 'mdkit-input-' . esc_attr( $name );
		unset( $attrs['id'] );

		$html = '<div class="mdkit-field">';

		if ( $label !== '' ) {
			$html .= '<label class="mdkit-field__label" for="' . $id . '">' . esc_html( $label ) . '</label>';
		}

		$attrParts = [
			'type="' . esc_attr( $type ) . '"',
			'id="' . $id . '"',
			'name="' . esc_attr( $name ) . '"',
			'value="' . esc_attr( $value ) . '"',
			'class="mdkit-input"',
		];

		foreach ( $attrs as $k => $v ) {
			$attrParts[] = esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
		}

		$html .= '<input ' . implode( ' ', $attrParts ) . '>';

		if ( $description !== '' ) {
			$html .= '<p class="mdkit-field__desc">' . esc_html( $description ) . '</p>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Select dropdown.
	 *
	 * @param string                $name        Select name.
	 * @param array<string,string>  $options     Value => label.
	 * @param string                $selected    Currently selected value.
	 * @param string                $label       Label text.
	 * @param string                $description Help text.
	 */
	public static function select(
		string $name,
		array $options,
		string $selected = '',
		string $label = '',
		string $description = '',
	): string {
		$id = 'mdkit-select-' . esc_attr( $name );

		$html = '<div class="mdkit-field">';

		if ( $label !== '' ) {
			$html .= '<label class="mdkit-field__label" for="' . $id . '">' . esc_html( $label ) . '</label>';
		}

		$html .= '<select id="' . $id . '" name="' . esc_attr( $name ) . '" class="mdkit-select">';

		foreach ( $options as $val => $lbl ) {
			$sel   = ( (string) $val === $selected ) ? ' selected' : '';
			$html .= '<option value="' . esc_attr( (string) $val ) . '"' . $sel . '>' . esc_html( $lbl ) . '</option>';
		}

		$html .= '</select>';

		if ( $description !== '' ) {
			$html .= '<p class="mdkit-field__desc">' . esc_html( $description ) . '</p>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Checkbox.
	 */
	public static function checkbox( string $name, bool $checked, string $label = '', string $description = '', string $value = '1' ): string {
		$id = 'mdkit-cb-' . esc_attr( $name ) . '-' . esc_attr( $value );

		$html  = '<div class="mdkit-field mdkit-field--checkbox">';
		$html .= '<label class="mdkit-checkbox" for="' . $id . '">';
		$html .= '<input type="checkbox" id="' . $id . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"'
				. ( $checked ? ' checked' : '' ) . ' class="mdkit-checkbox__input">';
		$html .= '<span class="mdkit-checkbox__mark"></span>';
		if ( $label !== '' ) {
			$html .= '<span class="mdkit-checkbox__label">' . esc_html( $label ) . '</span>';
		}
		$html .= '</label>';

		if ( $description !== '' ) {
			$html .= '<p class="mdkit-field__desc">' . esc_html( $description ) . '</p>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Textarea.
	 */
	public static function textarea(
		string $name,
		string $value = '',
		string $label = '',
		string $description = '',
		int $rows = 5,
	): string {
		$id = 'mdkit-textarea-' . esc_attr( $name );

		$html = '<div class="mdkit-field">';

		if ( $label !== '' ) {
			$html .= '<label class="mdkit-field__label" for="' . $id . '">' . esc_html( $label ) . '</label>';
		}

		$html .= '<textarea id="' . $id . '" name="' . esc_attr( $name ) . '" rows="' . $rows . '" class="mdkit-textarea">'
				. esc_textarea( $value ) . '</textarea>';

		if ( $description !== '' ) {
			$html .= '<p class="mdkit-field__desc">' . esc_html( $description ) . '</p>';
		}

		$html .= '</div>';

		return $html;
	}

	/* ------------------------------------------------------------------
	 * Alerts / Notices
	 * ----------------------------------------------------------------*/

	/**
	 * Alert panel.
	 *
	 * @param string $message     Alert text (may contain HTML).
	 * @param string $type        'info' | 'success' | 'warning' | 'error'.
	 * @param bool   $dismissible Whether the alert can be dismissed.
	 */
	public static function alert( string $message, string $type = 'info', bool $dismissible = false ): string {
		$iconMap = [
			'info'    => 'info',
			'success' => 'circle-check',
			'warning' => 'alert-triangle',
			'error'   => 'circle-x',
		];

		$iconName = $iconMap[ $type ] ?? 'info';

		$class = 'mdkit-alert mdkit-alert--' . esc_attr( $type );
		if ( $dismissible ) {
			$class .= ' mdkit-alert--dismissible';
		}

		$html  = '<div class="' . $class . '" role="alert">';
		$html .= '<span class="mdkit-alert__icon">' . Icons::render( $iconName, 20 ) . '</span>';
		$html .= '<div class="mdkit-alert__content">' . wp_kses_post( $message ) . '</div>';

		if ( $dismissible ) {
			$html .= '<button type="button" class="mdkit-alert__dismiss" aria-label="'
					. esc_attr__( 'Dismiss', 'mudrava-admin-tweaks' ) . '">'
					. Icons::render( 'x', 16 ) . '</button>';
		}

		$html .= '</div>';

		return $html;
	}

	/* ------------------------------------------------------------------
	 * Badges
	 * ----------------------------------------------------------------*/

	/**
	 * Status badge.
	 *
	 * @param string $text Label.
	 * @param string $type 'default' | 'success' | 'warning' | 'error' | 'info'.
	 */
	public static function badge( string $text, string $type = 'default' ): string {
		return '<span class="mdkit-badge mdkit-badge--' . esc_attr( $type ) . '">'
			. esc_html( $text )
			. '</span>';
	}

	/* ------------------------------------------------------------------
	 * Empty state
	 * ----------------------------------------------------------------*/

	/**
	 * Empty state placeholder.
	 */
	public static function emptyState( string $message, string $icon = 'inbox' ): string {
		$html  = '<div class="mdkit-empty-state">';
		$html .= '<div class="mdkit-empty-state__icon">' . Icons::render( $icon, 48 ) . '</div>';
		$html .= '<p class="mdkit-empty-state__text">' . esc_html( $message ) . '</p>';
		$html .= '</div>';

		return $html;
	}

	/* ------------------------------------------------------------------
	 * Preloader
	 * ----------------------------------------------------------------*/

	/**
	 * Loading spinner.
	 */
	public static function preloader( string $size = 'md' ): string {
		return '<div class="mdkit-preloader mdkit-preloader--' . esc_attr( $size ) . '">'
			. Icons::render( 'loader', $size === 'sm' ? 16 : ( $size === 'lg' ? 32 : 24 ), 'mdkit-spin' )
			. '</div>';
	}

	/* ------------------------------------------------------------------
	 * Nonce field helper
	 * ----------------------------------------------------------------*/

	/**
	 * Hidden nonce field.
	 */
	public static function nonceField( string $action ): string {
		return wp_nonce_field( $action, '_wpnonce', true, false );
	}

	/* ------------------------------------------------------------------
	 * Section helper
	 * ----------------------------------------------------------------*/

	/**
	 * A titled section within a card or page.
	 */
	public static function section( string $title, string $body, string $description = '' ): string {
		$html  = '<div class="mdkit-section">';
		$html .= '<div class="mdkit-section__header">';
		$html .= '<h3 class="mdkit-section__title">' . esc_html( $title ) . '</h3>';

		if ( $description !== '' ) {
			$html .= '<p class="mdkit-section__desc">' . esc_html( $description ) . '</p>';
		}

		$html .= '</div>';
		$html .= '<div class="mdkit-section__body">' . $body . '</div>';
		$html .= '</div>';

		return $html;
	}

	/* ------------------------------------------------------------------
	 * Confirm dialog (JS-powered)
	 * ----------------------------------------------------------------*/

	/**
	 * Render a hidden confirm dialog activated via JS.
	 *
	 * Trigger with `data-mdkit-confirm="<id>"` on any button/link.
	 *
	 * @param string $id           Unique dialog ID.
	 * @param string $message      Confirmation message.
	 * @param string $confirmLabel Text for the confirm button.
	 * @param string $type         'danger' | 'warning'. Default 'danger'.
	 */
	public static function confirmDialog(
		string $id,
		string $message,
		string $confirmLabel = '',
		string $type = 'danger',
	): string {
		if ( $confirmLabel === '' ) {
			$confirmLabel = __( 'Confirm', 'mudrava-admin-tweaks' );
		}

		$html  = '<div class="mdkit-dialog" id="mdkit-dialog-' . esc_attr( $id ) . '" role="dialog" aria-modal="true" hidden>';
		$html .= '<div class="mdkit-dialog__overlay"></div>';
		$html .= '<div class="mdkit-dialog__panel">';
		$html .= '<div class="mdkit-dialog__icon">' . Icons::render( $type === 'danger' ? 'alert-triangle' : 'info', 24 ) . '</div>';
		$html .= '<p class="mdkit-dialog__message">' . esc_html( $message ) . '</p>';
		$html .= '<div class="mdkit-dialog__actions">';
		$html .= self::button( __( 'Cancel', 'mudrava-admin-tweaks' ), 'secondary', [ 'class' => 'mdkit-dialog__cancel' ] );
		$html .= self::button( $confirmLabel, $type, [ 'class' => 'mdkit-dialog__confirm' ] );
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/* ------------------------------------------------------------------
	 * Data Table (AJAX-powered)
	 * ----------------------------------------------------------------*/

	/**
	 * Render a data table container with toolbar and pagination.
	 *
	 * The actual rows are loaded via AJAX by admin.js `initDataTables()`.
	 * The PHP AJAX handler returns JSON with `rows` (HTML) and `total`.
	 *
	 * @param string               $id      Unique table ID.
	 * @param array<string,string> $columns Column key => label map.
	 * @param array{
	 *     ajaxAction:  string,
	 *     nonce:       string,
	 *     perPage?:    int,
	 *     searchable?: bool,
	 *     filters?:    array<string, array{label:string, options:array<string,string>}>,
	 *     sortable?:   string[],
	 *     defaultSort?:string,
	 *     defaultOrder?:string,
	 * } $options Table options.
	 * @return string
	 */
	public static function dataTable( string $id, array $columns, array $options = [] ): string {
		$perPage   = $options['perPage'] ?? 20;
		$sortable  = $options['sortable'] ?? [];
		$defSort   = $options['defaultSort'] ?? '';
		$defOrder  = $options['defaultOrder'] ?? 'asc';
		$searchable = $options['searchable'] ?? true;
		$filters   = $options['filters'] ?? [];

		// Data attributes for JS controller.
		$attrs  = ' data-mdkit-table="' . esc_attr( $id ) . '"';
		$attrs .= ' data-action="' . esc_attr( $options['ajaxAction'] ?? '' ) . '"';
		$attrs .= ' data-nonce="' . esc_attr( $options['nonce'] ?? '' ) . '"';
		$attrs .= ' data-per-page="' . (int) $perPage . '"';
		$attrs .= ' data-sort="' . esc_attr( $defSort ) . '"';
		$attrs .= ' data-order="' . esc_attr( $defOrder ) . '"';

		$html = '<div class="mdkit-data-table"' . $attrs . '>';

		// ------ Toolbar (search + filters) ------
		$html .= '<div class="mdkit-data-table__toolbar">';

		if ( ! empty( $filters ) ) {
			foreach ( $filters as $filterKey => $filter ) {
				$html .= '<select class="mdkit-data-table__filter" data-filter="' . esc_attr( $filterKey ) . '">';
				foreach ( $filter['options'] as $value => $label ) {
					$html .= '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
				}
				$html .= '</select>';
			}
		}

		if ( $searchable ) {
			$html .= '<div class="mdkit-data-table__search">';
			$html .= '<input type="search" class="mdkit-data-table__search-input" placeholder="' . esc_attr__( 'Search…', 'mudrava-admin-tweaks' ) . '" />';
			$html .= '</div>';
		}

		$html .= '</div>'; // toolbar

		// ------ Table ------
		$html .= '<div class="mdkit-data-table__wrapper">';
		$html .= '<table class="mdkit-data-table__table">';

		// <thead>
		$html .= '<thead><tr>';
		foreach ( $columns as $colKey => $colLabel ) {
			$sortAttr = '';
			$sortClass = '';
			if ( in_array( $colKey, $sortable, true ) ) {
				$sortAttr  = ' data-sort-key="' . esc_attr( $colKey ) . '"';
				$sortClass = ' mdkit-data-table__th--sortable';
				if ( $colKey === $defSort ) {
					$sortClass .= ' mdkit-data-table__th--sorted mdkit-data-table__th--' . esc_attr( $defOrder );
				}
			}
			$html .= '<th class="mdkit-data-table__th' . $sortClass . '"' . $sortAttr . '>';
			$html .= esc_html( $colLabel );
			if ( $sortAttr !== '' ) {
				$html .= '<span class="mdkit-data-table__sort-icon"></span>';
			}
			$html .= '</th>';
		}
		$html .= '</tr></thead>';

		// <tbody> — populated via AJAX
		$html .= '<tbody class="mdkit-data-table__body">';
		$html .= '<tr><td colspan="' . count( $columns ) . '" class="mdkit-data-table__loading">';
		$html .= self::preloader();
		$html .= '</td></tr>';
		$html .= '</tbody>';

		$html .= '</table>';
		$html .= '</div>'; // wrapper

		// ------ Pagination ------
		$html .= '<div class="mdkit-data-table__pagination">';
		$html .= '<span class="mdkit-data-table__info"></span>';
		$html .= '<div class="mdkit-data-table__pages"></div>';
		$html .= '</div>';

		$html .= '</div>'; // mdkit-data-table

		return $html;
	}
}
