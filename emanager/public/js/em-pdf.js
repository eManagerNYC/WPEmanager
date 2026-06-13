/**
 * eManager — PDF export via jsPDF + autotable.
 */
( function ( EM ) {
	'use strict';

	function doc() {
		const { jsPDF } = window.jspdf;
		return new jsPDF( { unit: 'pt', format: 'letter' } );
	}

	function header( pdf, title ) {
		const project = ( EM.app.boot.project || {} );
		pdf.setFontSize( 16 );
		pdf.text( title, 40, 50 );
		pdf.setFontSize( 9 );
		pdf.setTextColor( 120 );
		const meta = [ project.name, project.number, new Date().toLocaleString() ].filter( Boolean ).join( '  ·  ' );
		pdf.text( meta, 40, 66 );
		pdf.setTextColor( 0 );
		return 84;
	}

	EM.pdf = {
		/** Export the current list view (table state) to PDF. */
		fromList( state ) {
			const pdf = doc();
			const y = header( pdf, state.module.name );
			const cols = [ ...EM.table.columns( state.module ), { name: 'status', label: 'Status' }, { name: 'created_at', label: 'Created' } ];

			pdf.autoTable( {
				startY: y,
				head: [ cols.map( ( c ) => c.label ) ],
				body: state.records.map( ( record ) => cols.map( ( c ) => {
					const v = record[ c.name ];
					if ( c.name === 'created_at' && v ) return new Date( v ).toLocaleDateString();
					return v === null || v === undefined ? '' : String( v );
				} ) ),
				styles: { fontSize: 8, cellPadding: 4 },
				headStyles: { fillColor: [ 33, 37, 41 ] },
			} );

			pdf.save( `${ state.module.id }-list.pdf` );
		},

		/** Export a single record (view page) to PDF, embedding signatures. */
		fromRecord( module, record ) {
			const pdf = doc();
			const y = header( pdf, module.name + ' — Record' );
			const fields = module.fields || [];

			const rows = fields
				.filter( ( field ) => field.type !== 'signature' )
				.map( ( field ) => {
					let value = record[ field.name ];
					if ( value === null || value === undefined ) value = '';
					if ( field.type === 'checkbox' ) value = value ? 'Yes' : 'No';
					return [ field.label, String( value ) ];
				} );
			rows.push( [ 'Status', record.status || '' ] );
			rows.push( [ 'Created', record.created_at ? new Date( record.created_at ).toLocaleString() : '' ] );
			rows.push( [ 'Created by', record.created_by_name || '' ] );

			pdf.autoTable( {
				startY: y,
				head: [ [ 'Field', 'Value' ] ],
				body: rows,
				styles: { fontSize: 9, cellPadding: 5, overflow: 'linebreak' },
				headStyles: { fillColor: [ 33, 37, 41 ] },
				columnStyles: { 0: { cellWidth: 150, fontStyle: 'bold' } },
			} );

			// Embed signature images below the table.
			const signatures = fields.filter(
				( field ) => field.type === 'signature' && /^data:image\/png;base64,/.test( record[ field.name ] || '' )
			);
			let cursor = pdf.lastAutoTable.finalY + 24;
			for ( const field of signatures ) {
				if ( cursor + 90 > pdf.internal.pageSize.getHeight() - 40 ) {
					pdf.addPage();
					cursor = 50;
				}
				pdf.setFontSize( 10 );
				pdf.text( field.label, 40, cursor );
				pdf.setDrawColor( 180 );
				pdf.rect( 40, cursor + 6, 200, 64 );
				try {
					pdf.addImage( record[ field.name ], 'PNG', 42, cursor + 8, 196, 60 );
				} catch ( e ) {
					pdf.text( '(signature could not be rendered)', 46, cursor + 40 );
				}
				cursor += 92;
			}

			pdf.save( `${ module.id }-${ record.id }.pdf` );
		},

		/** Export arbitrary stats rows (Reports module). */
		fromRows( title, head, body, filename ) {
			const pdf = doc();
			const y = header( pdf, title );
			pdf.autoTable( {
				startY: y,
				head: [ head ],
				body,
				styles: { fontSize: 9, cellPadding: 5 },
				headStyles: { fillColor: [ 33, 37, 41 ] },
			} );
			pdf.save( filename );
		},
	};
} )( window.EM );
