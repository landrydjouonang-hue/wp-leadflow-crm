/**
 * WP LeadFlow CRM — dashboard charts.
 *
 * A small, dependency-free SVG renderer for the charts the dashboard needs:
 * bar (single or grouped), combo (bars + line) and doughnut. Every chart
 * also has a server-rendered data table (the accessible alternative), and
 * every bar, point and slice is keyboard-focusable with a tooltip.
 *
 * Configuration (from `data-lf-chart`):
 *   { type, labels[], datasets[{ label, data[], color?, colors[]?, type?: 'line', display[]? }],
 *     format: { style: 'number'|'currency', currency? }, center?, center_label? }
 */
( () => {
	'use strict';

	const { __, sprintf } = wp.i18n;
	const NS = 'http://www.w3.org/2000/svg';
	const LOCALE = document.documentElement.lang || undefined;
	const HEIGHT = 260;

	/**
	 * Creates an SVG element.
	 *
	 * @param {string}  name   Tag.
	 * @param {Object}  attrs  Attributes.
	 * @param {Element} parent Parent to append to.
	 * @return {SVGElement} Element.
	 */
	const svg = ( name, attrs = {}, parent = null ) => {
		const node = document.createElementNS( NS, name );

		Object.entries( attrs ).forEach( ( [ key, value ] ) => node.setAttribute( key, String( value ) ) );

		if ( parent ) {
			parent.appendChild( node );
		}

		return node;
	};

	/**
	 * Number formatter for a chart format.
	 *
	 * @param {Object}  format  Format config.
	 * @param {boolean} compact Compact notation (axis labels).
	 * @return {Function} value => string.
	 */
	const formatter = ( format, compact ) => {
		const options = 'currency' === format.style
			? { style: 'currency', currency: format.currency || 'USD', maximumFractionDigits: compact ? 1 : 0 }
			: { maximumFractionDigits: 1 };

		if ( compact ) {
			options.notation = 'compact';
		}

		try {
			const intl = new Intl.NumberFormat( LOCALE, options );
			return ( value ) => intl.format( value );
		} catch ( error ) {
			return ( value ) => String( value );
		}
	};

	/**
	 * Rounded axis maximum and step.
	 *
	 * @param {number}  max      Largest value.
	 * @param {boolean} integers Whole-number steps only.
	 * @return {{max: number, step: number}} Scale.
	 */
	const scale = ( max, integers ) => {
		if ( max <= 0 ) {
			return { max: integers ? 4 : 1, step: integers ? 1 : 0.25 };
		}

		const raw = max / 4;
		const magnitude = Math.pow( 10, Math.floor( Math.log10( raw ) ) );
		const normalized = raw / magnitude;
		let step = ( normalized <= 1 ? 1 : normalized <= 2 ? 2 : normalized <= 5 ? 5 : 10 ) * magnitude;

		if ( integers ) {
			step = Math.max( 1, Math.ceil( step ) );
		}

		return { max: Math.ceil( max / step ) * step, step };
	};

	/**
	 * Display value of a data point.
	 *
	 * @param {Object}   dataset Dataset.
	 * @param {number}   index   Index.
	 * @param {Function} format  Formatter.
	 * @return {string} Text.
	 */
	const display = ( dataset, index, format ) =>
		dataset.display && undefined !== dataset.display[ index ] ? dataset.display[ index ] : format( dataset.data[ index ] || 0 );

	/**
	 * Truncates a label to a number of characters.
	 *
	 * @param {string} text  Label.
	 * @param {number} chars Maximum characters.
	 * @return {string} Text.
	 */
	const truncate = ( text, chars ) => ( text.length > chars ? text.slice( 0, Math.max( 1, chars - 1 ) ) + '…' : text );

	/**
	 * Tooltip shared by the marks of one chart.
	 *
	 * @param {HTMLElement} container Chart container.
	 * @return {{show: Function, hide: Function}} API.
	 */
	const tooltip = ( container ) => {
		const tip = document.createElement( 'div' );
		tip.className = 'lf-chart__tooltip';
		tip.hidden = true;
		container.appendChild( tip );

		return {
			show( mark, lines ) {
				tip.replaceChildren(
					...lines.map( ( line, i ) => {
						const row = document.createElement( i ? 'span' : 'strong' );
						row.textContent = line;
						return row;
					} )
				);
				tip.hidden = false;

				const box = container.getBoundingClientRect();
				const rect = mark.getBoundingClientRect();
				const left = Math.min( Math.max( rect.left - box.left + rect.width / 2 - tip.offsetWidth / 2, 0 ), box.width - tip.offsetWidth );

				tip.style.left = `${ left }px`;
				tip.style.top = `${ Math.max( rect.top - box.top - tip.offsetHeight - 8, 0 ) }px`;
			},
			hide() {
				tip.hidden = true;
			},
		};
	};

	/**
	 * Makes a mark focusable and wires its tooltip.
	 *
	 * @param {SVGElement} mark  Bar, point or slice.
	 * @param {string[]}   lines Tooltip lines (first = title).
	 * @param {Object}     tip   Tooltip API.
	 */
	const interactive = ( mark, lines, tip ) => {
		mark.setAttribute( 'tabindex', '0' );
		mark.setAttribute( 'role', 'img' );
		mark.setAttribute( 'aria-label', lines.join( ', ' ) );
		mark.classList.add( 'lf-chart__mark' );

		const show = () => tip.show( mark, lines );

		mark.addEventListener( 'mouseenter', show );
		mark.addEventListener( 'focus', show );
		mark.addEventListener( 'mouseleave', tip.hide );
		mark.addEventListener( 'blur', tip.hide );
	};

	/**
	 * Legend below a chart.
	 *
	 * @param {HTMLElement} container Container.
	 * @param {Object[]}    items     { label, color, value? }.
	 */
	const legend = ( container, items ) => {
		const list = document.createElement( 'ul' );
		list.className = 'lf-chart__legend';

		items.forEach( ( item ) => {
			const li = document.createElement( 'li' );
			const swatch = document.createElement( 'span' );

			swatch.className = 'lf-chart__swatch' + ( item.line ? ' is-line' : '' );
			swatch.style.background = item.color;
			swatch.setAttribute( 'aria-hidden', 'true' );
			li.append( swatch, document.createTextNode( item.label ) );

			if ( item.value ) {
				const value = document.createElement( 'span' );
				value.className = 'lf-chart__legend-value';
				value.textContent = item.value;
				li.appendChild( value );
			}

			list.appendChild( li );
		} );

		container.appendChild( list );
	};

	/**
	 * Bar and combo (bars + line) charts.
	 *
	 * @param {HTMLElement} container Container.
	 * @param {Object}      config    Chart config.
	 * @param {Object}      tip       Tooltip API.
	 * @param {SVGElement}  root      SVG root.
	 * @param {number}      width     Width.
	 */
	const drawBars = ( container, config, tip, root, width ) => {
		const format = formatter( config.format, false );
		const axisFormat = formatter( config.format, true );
		const bars = config.datasets.filter( ( d ) => 'line' !== d.type );
		const lines = config.datasets.filter( ( d ) => 'line' === d.type );
		const values = config.datasets.flatMap( ( d ) => d.data.map( Number ) );
		const integers = 'currency' !== config.format.style;
		// Small counts get a 0–4 axis instead of a single step.
		const { max, step } = scale( Math.max( integers ? 4 : 0, ...values ), integers );

		const margin = { top: 12, right: 8, bottom: 40, left: 'currency' === config.format.style ? 64 : 40 };
		const plotW = width - margin.left - margin.right;
		const plotH = HEIGHT - margin.top - margin.bottom;
		const count = config.labels.length;
		const band = plotW / Math.max( 1, count );
		const y = ( value ) => margin.top + plotH - ( value / max ) * plotH;

		// Grid and y axis.
		const grid = svg( 'g', { class: 'lf-chart__grid', 'aria-hidden': 'true' }, root );

		for ( let tick = 0; tick <= max + step / 2; tick += step ) {
			svg( 'line', { x1: margin.left, x2: width - margin.right, y1: y( tick ), y2: y( tick ) }, grid );
			svg( 'text', { x: margin.left - 8, y: y( tick ) + 4, 'text-anchor': 'end' }, grid ).textContent = axisFormat( tick );
		}

		// X labels (skipped and truncated when crowded).
		const every = Math.max( 1, Math.ceil( 56 / band ) );
		const labels = svg( 'g', { class: 'lf-chart__labels', 'aria-hidden': 'true' }, root );

		config.labels.forEach( ( label, i ) => {
			if ( i % every ) {
				return;
			}

			const text = svg( 'text', { x: margin.left + band * i + band / 2, y: HEIGHT - margin.bottom + 18, 'text-anchor': 'middle' }, labels );
			text.textContent = truncate( String( label ), Math.max( 4, Math.floor( ( band * every ) / 7 ) ) );
			svg( 'title', {}, text ).textContent = label;
		} );

		// Bars.
		const inner = band * 0.7;
		const barW = Math.max( 2, Math.min( 48, inner / Math.max( 1, bars.length ) ) );
		const group = svg( 'g', { class: 'lf-chart__bars' }, root );

		bars.forEach( ( dataset, d ) => {
			dataset.data.forEach( ( raw, i ) => {
				const value = Number( raw ) || 0;
				const x = margin.left + band * i + ( band - barW * bars.length ) / 2 + d * barW;
				const top = y( value );
				const rect = svg(
					'rect',
					{
						x,
						y: top,
						width: Math.max( 1, barW - 2 ),
						height: Math.max( 0, margin.top + plotH - top ),
						rx: 3,
						fill: ( dataset.colors && dataset.colors[ i ] ) || dataset.color || '#4f46e5',
					},
					group
				);

				interactive( rect, [ config.labels[ i ], `${ dataset.label }: ${ display( dataset, i, format ) }` ], tip );
			} );
		} );

		// Lines.
		lines.forEach( ( dataset ) => {
			const points = dataset.data.map( ( raw, i ) => [ margin.left + band * i + band / 2, y( Number( raw ) || 0 ) ] );
			const color = dataset.color || '#16a34a';

			svg( 'path', { d: points.map( ( p, i ) => `${ i ? 'L' : 'M' }${ p[ 0 ] },${ p[ 1 ] }` ).join( ' ' ), fill: 'none', stroke: color, 'stroke-width': 2.5, class: 'lf-chart__line', 'aria-hidden': 'true' }, root );

			points.forEach( ( p, i ) => {
				const dot = svg( 'circle', { cx: p[ 0 ], cy: p[ 1 ], r: 4.5, fill: '#fff', stroke: color, 'stroke-width': 2.5 }, root );
				interactive( dot, [ config.labels[ i ], `${ dataset.label }: ${ display( dataset, i, format ) }` ], tip );
			} );
		} );

		if ( config.datasets.length > 1 ) {
			legend(
				container,
				config.datasets.map( ( d ) => ( { label: d.label, color: d.color || '#4f46e5', line: 'line' === d.type } ) )
			);
		}
	};

	/**
	 * Arc path of a doughnut slice.
	 *
	 * @param {number} cx    Center x.
	 * @param {number} cy    Center y.
	 * @param {number} r     Outer radius.
	 * @param {number} ri    Inner radius.
	 * @param {number} start Start angle (radians).
	 * @param {number} end   End angle (radians).
	 * @return {string} Path data.
	 */
	const arc = ( cx, cy, r, ri, start, end ) => {
		// A full circle cannot be drawn with one arc: split it.
		if ( end - start >= Math.PI * 2 - 0.0001 ) {
			const mid = start + Math.PI;
			return arc( cx, cy, r, ri, start, mid ) + ' ' + arc( cx, cy, r, ri, mid, end );
		}

		const large = end - start > Math.PI ? 1 : 0;
		const p = ( radius, angle ) => `${ cx + radius * Math.cos( angle ) },${ cy + radius * Math.sin( angle ) }`;

		return `M${ p( r, start ) } A${ r },${ r } 0 ${ large } 1 ${ p( r, end ) } L${ p( ri, end ) } A${ ri },${ ri } 0 ${ large } 0 ${ p( ri, start ) } Z`;
	};

	/**
	 * Doughnut chart with a legend.
	 *
	 * @param {HTMLElement} container Container.
	 * @param {Object}      config    Chart config.
	 * @param {Object}      tip       Tooltip API.
	 * @param {SVGElement}  root      SVG root.
	 * @param {number}      width     Width.
	 */
	const drawDoughnut = ( container, config, tip, root, width ) => {
		const format = formatter( config.format, false );
		const dataset = config.datasets[ 0 ];
		const values = dataset.data.map( ( v ) => Math.max( 0, Number( v ) || 0 ) );
		const total = values.reduce( ( a, b ) => a + b, 0 );
		const size = Math.min( width, 220 );
		const cx = width / 2;
		const cy = HEIGHT / 2 - 10;
		const r = size / 2 - 4;
		const ri = r * 0.62;
		const colors = config.labels.map( ( label, i ) => ( dataset.colors && dataset.colors[ i ] ) || '#4f46e5' );
		const percent = ( value ) => ( total ? Math.round( ( value / total ) * 1000 ) / 10 : 0 );
		let angle = -Math.PI / 2;

		values.forEach( ( value, i ) => {
			if ( value <= 0 ) {
				return;
			}

			const end = angle + ( value / total ) * Math.PI * 2;
			const slice = svg( 'path', { d: arc( cx, cy, r, ri, angle, end ), fill: colors[ i ] }, root );

			interactive(
				slice,
				[ config.labels[ i ], `${ display( dataset, i, format ) } (${ sprintf( /* translators: %s: Percentage. */ __( '%s%%', 'wp-leadflow-crm' ), percent( value ) ) })` ],
				tip
			);
			angle = end;
		} );

		if ( config.center ) {
			const center = svg( 'g', { class: 'lf-chart__center', 'aria-hidden': 'true' }, root );
			svg( 'text', { x: cx, y: cy + 4, 'text-anchor': 'middle', class: 'lf-chart__center-value' }, center ).textContent = config.center;

			if ( config.center_label ) {
				svg( 'text', { x: cx, y: cy + 24, 'text-anchor': 'middle', class: 'lf-chart__center-label' }, center ).textContent = config.center_label;
			}
		}

		root.setAttribute( 'viewBox', `0 0 ${ width } ${ cy * 2 + 20 }` );
		root.setAttribute( 'height', String( cy * 2 + 20 ) );

		legend(
			container,
			config.labels.map( ( label, i ) => ( {
				label,
				color: colors[ i ],
				value: `${ display( dataset, i, format ) } · ${ percent( values[ i ] ) }%`,
			} ) )
		);
	};

	/**
	 * Renders (or re-renders) one chart.
	 *
	 * @param {HTMLElement} container Element with data-lf-chart.
	 */
	const render = ( container ) => {
		let config;

		try {
			config = JSON.parse( container.dataset.lfChart );
		} catch ( error ) {
			return;
		}

		const width = Math.max( 260, Math.floor( container.clientWidth ) );

		if ( container.dataset.renderedWidth === String( width ) ) {
			return;
		}

		container.dataset.renderedWidth = String( width );
		container.replaceChildren();

		const tip = tooltip( container );
		const root = svg( 'svg', {
			viewBox: `0 0 ${ width } ${ HEIGHT }`,
			width: '100%',
			height: HEIGHT,
			role: 'group',
			'aria-labelledby': container.dataset.titleId,
			'aria-describedby': container.dataset.descId,
			class: 'lf-chart__svg',
			focusable: 'false',
		} );

		container.prepend( root );

		if ( 'doughnut' === config.type ) {
			drawDoughnut( container, config, tip, root, width );
		} else {
			drawBars( container, config, tip, root, width );
		}
	};

	/**
	 * Period filter: custom dates only when "Custom range" is selected.
	 */
	const initFilters = () => {
		const form = document.querySelector( '[data-lf-dashboard-filters]' );

		if ( ! form ) {
			return;
		}

		const range = form.querySelector( '[data-lf-range]' );
		const dates = form.querySelector( '[data-lf-custom-dates]' );
		const sync = () => {
			dates.hidden = 'custom' !== range.value;
		};

		range.addEventListener( 'change', () => {
			sync();

			if ( 'custom' !== range.value ) {
				form.requestSubmit();
			}
		} );

		dates.querySelectorAll( 'input' ).forEach( ( input ) => input.addEventListener( 'change', () => {
			range.value = 'custom';
		} ) );

		sync();
	};

	const init = () => {
		initFilters();

		const charts = document.querySelectorAll( '[data-lf-chart]' );

		charts.forEach( ( container ) => {
			render( container );

			// The chart is drawn: the data table becomes an optional view.
			const table = container.parentElement.querySelector( '[data-lf-chart-table]' );

			if ( table ) {
				table.open = false;
			}
		} );

		if ( 'ResizeObserver' in window ) {
			let frame = 0;
			const observer = new ResizeObserver( ( entries ) => {
				window.cancelAnimationFrame( frame );
				frame = window.requestAnimationFrame( () => entries.forEach( ( entry ) => render( entry.target ) ) );
			} );

			charts.forEach( ( container ) => observer.observe( container ) );
		}
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
