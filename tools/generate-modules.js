/**
 * eManager build tool — generates module.json files and supabase-schema.sql
 * from one compact specification. Run: node tools/generate-modules.js
 */
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );

const ROOT = path.join( __dirname, '..', 'emanager' );

// Field shorthand: f(name, label, type, extra)
const f = ( name, label, type = 'text', extra = {} ) => ( { name, label, type, ...extra } );

/**
 * Workflow shorthand. Pass an ordered map of state-name => [transitions].
 * The first state is the initial status. Each transition is
 * { to, label, party:[party roles], directions:[...], cap }.
 * Returns { initial, states: { name: { transitions } } } and the derived
 * status list (so list filters and badges match the workflow states).
 */
const wf = ( states ) => {
	const out = { initial: Object.keys( states )[ 0 ], states: {} };
	for ( const [ name, transitions ] of Object.entries( states ) ) {
		out.states[ name ] = { transitions };
	}
	return out;
};

// Frequently reused lookups (Resources section is relational).
const LOOKUP_LOCATION = { lookup: { module: 'locations', value: 'name', label: 'name' } };
const LOOKUP_COSTCODE = { lookup: { module: 'cost-codes', value: 'code', label: 'title' } };
const LOOKUP_CSI = { lookup: { module: 'csi-divisions', value: 'number', label: 'title' } };

// Reusable Resources workflows: active/inactive toggle, and rate versioning.
const WF_ACTIVE = wf( {
	'Active': [ { to: 'Inactive', label: 'Deactivate', party: [ 'gc' ] } ],
	'Inactive': [ { to: 'Active', label: 'Reactivate', party: [ 'gc' ] } ],
} );
const WF_RATE = wf( {
	'Active': [ { to: 'Superseded', label: 'Supersede (new rate)', party: [ 'gc' ] } ],
	'Superseded': [],
} );

const MODULES = [
	/* ------------------------------------------------------------------ Preconstruction */
	{
		section: 'preconstruction', id: 'qualified-bidders', name: 'Qualified Bidders', icon: 'bi-people',
		workflow: wf( {
			'Pending Review': [
				{ to: 'Qualified', label: 'Qualify', party: [ 'gc' ] },
				{ to: 'Conditionally Qualified', label: 'Qualify w/ conditions', party: [ 'gc' ] },
				{ to: 'Not Qualified', label: 'Decline', party: [ 'gc' ] },
			],
			'Qualified': [],
			'Conditionally Qualified': [ { to: 'Qualified', label: 'Clear conditions', party: [ 'gc' ] } ],
			'Not Qualified': [],
		} ),
		fields: [
			f( 'company_name', 'Company name', 'text', { required: true, list: true } ),
			f( 'trade', 'Trade / scope', 'select', { ...LOOKUP_CSI, list: true } ),
			f( 'contact_name', 'Contact', 'text', { list: true } ),
			f( 'email', 'Email', 'email' ),
			f( 'phone', 'Phone', 'text' ),
			f( 'emr', 'EMR rating', 'number', { step: '0.01' } ),
			f( 'bonding_capacity', 'Bonding capacity', 'currency', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'preconstruction', id: 'bid-packages', name: 'Bid Packages', icon: 'bi-box-seam',
		// Package → out to bid (issue a solicitation) → bids received → awarded.
		workflow: wf( {
			'Draft': [ { to: 'Out to Bid', label: 'Issue to bid', party: [ 'gc' ] } ],
			'Out to Bid': [ { to: 'Bids Received', label: 'Bids received', party: [ 'gc' ] } ],
			'Bids Received': [ { to: 'Awarded', label: 'Award', party: [ 'gc' ] } ],
			'Awarded': [ { to: 'Closed', label: 'Close', party: [ 'gc' ] } ],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'bid-solicitations', label: 'Issue invitation to bid', map: { title: 'title', bid_package: 'number', scope: 'scope', estimate: 'estimate' } },
		],
		fields: [
			f( 'number', 'Package number', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'csi_division', 'CSI division', 'select', LOOKUP_CSI ),
			f( 'bid_due', 'Bids due', 'date', { list: true } ),
			f( 'estimate', 'Estimated value', 'currency', { list: true } ),
			f( 'scope', 'Scope description', 'textarea' ),
		],
	},
	{
		section: 'preconstruction', id: 'bid-manual', name: 'Bid Manual', icon: 'bi-journal-text',
		workflow: wf( {
			'Draft': [ { to: 'Published', label: 'Publish', party: [ 'gc' ] } ],
			'Published': [ { to: 'Superseded', label: 'Supersede', party: [ 'gc' ] } ],
			'Superseded': [],
		} ),
		fields: [
			f( 'title', 'Document title', 'text', { required: true, list: true } ),
			f( 'section_no', 'Manual section', 'text', { list: true } ),
			f( 'revision', 'Revision', 'text', { list: true } ),
			f( 'issue_date', 'Issue date', 'date', { list: true } ),
			f( 'file_url', 'Document link', 'url' ),
			f( 'summary', 'Summary', 'textarea' ),
		],
	},

	/* ------------------------------------------------------------------ Engineering */
	{
		section: 'engineering', id: 'rfis', name: 'RFIs', icon: 'bi-question-circle',
		// Ball-in-court RFI lifecycle: GC raises → routed to the consultant for
		// response → requester reviews → closed. Cost/schedule impact captured,
		// and an impacted RFI can spawn a Change Event (mirrors the CO chain).
		workflow: wf( {
			'Draft': [ { to: 'Open', label: 'Submit RFI', party: [ 'gc' ] } ],
			'Open': [
				{ to: 'Answered', label: 'Respond', party: [ 'consultant' ] },
				{ to: 'Void', label: 'Void', party: [ 'gc' ] },
			],
			'Answered': [
				{ to: 'Closed', label: 'Accept & close', party: [ 'gc' ] },
				{ to: 'Open', label: 'Reopen / clarify', party: [ 'gc' ] },
			],
			'Closed': [],
			'Void': [],
		} ),
		relations: [
			{ spawn: 'change-events', label: 'Raise change event', map: { title: 'subject', description: 'question' } },
		],
		fields: [
			f( 'number', 'RFI number', 'text', { required: true, list: true } ),
			f( 'subject', 'Subject', 'text', { required: true, list: true } ),
			f( 'discipline', 'Discipline', 'select', { options: [ 'Architectural', 'Structural', 'Civil', 'Mechanical', 'Electrical', 'Plumbing', 'Fire Protection', 'Other' ], list: true } ),
			f( 'priority', 'Priority', 'select', { options: [ 'Low', 'Normal', 'High', 'Critical' ], list: true } ),
			f( 'question', 'Question', 'textarea', { required: true } ),
			f( 'ball_in_court', 'Ball in court', 'text', { list: true, help: 'Party currently responsible for the next action.' } ),
			f( 'assigned_to', 'Assigned reviewer (Consultant)', 'text' ),
			f( 'date_submitted', 'Date submitted', 'date', { list: true } ),
			f( 'date_required', 'Response required by', 'date', { list: true } ),
			f( 'drawing_ref', 'Drawing / spec reference', 'text' ),
			f( 'answer', 'Official response', 'textarea' ),
			f( 'responded_by', 'Responded by', 'text' ),
			f( 'response_date', 'Response date', 'date' ),
			f( 'cost_impact', 'Cost impact?', 'checkbox' ),
			f( 'cost_impact_amount', 'Estimated cost impact', 'currency' ),
			f( 'schedule_impact', 'Schedule impact?', 'checkbox' ),
			f( 'schedule_impact_days', 'Schedule impact (days)', 'number' ),
			f( 'distribution', 'Distribution', 'textarea' ),
			f( 'file_url', 'Attachment link', 'url' ),
		],
	},
	{
		section: 'engineering', id: 'submittals', name: 'Submittals', icon: 'bi-file-earmark-arrow-up',
		// CSI/AIA submittal review: subcontractor submits → GC checks completeness
		// and forwards → A/E reviews and returns a disposition (Approved / Approved
		// as Noted / Revise & Resubmit / Rejected). Revise & Resubmit loops back.
		workflow: wf( {
			'Draft': [ { to: 'Submitted', label: 'Submit', party: [ 'subcontractor' ] } ],
			'Submitted': [
				{ to: 'GC Review', label: 'Begin GC review', party: [ 'gc' ] },
				{ to: 'Draft', label: 'Return to subcontractor', party: [ 'gc' ] },
			],
			'GC Review': [
				{ to: 'A/E Review', label: 'Forward to A/E', party: [ 'gc' ] },
				{ to: 'Draft', label: 'Return to subcontractor', party: [ 'gc' ] },
			],
			'A/E Review': [
				{ to: 'Returned', label: 'Return disposition', party: [ 'consultant' ], directions: [ 'Approved', 'Approved as Noted', 'Revise & Resubmit', 'Rejected' ] },
			],
			'Returned': [
				{ to: 'Closed', label: 'Close (approved)', party: [ 'gc' ] },
				{ to: 'Draft', label: 'Revise & resubmit', party: [ 'gc', 'subcontractor' ] },
			],
			'Closed': [],
		} ),
		fields: [
			f( 'number', 'Submittal number', 'text', { required: true, list: true } ),
			f( 'revision', 'Revision', 'text', { list: true } ),
			f( 'submittal_type', 'Type', 'select', { options: [ 'Shop Drawing', 'Product Data', 'Sample', 'Mock-up', 'Certificate', 'Test Report', 'O&M Data', 'Closeout' ], list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'spec_section', 'Spec section', 'text', { list: true } ),
			f( 'subcontractor', 'Subcontractor', 'text', { list: true } ),
			f( 'ball_in_court', 'Ball in court', 'text', { list: true } ),
			f( 'reviewer', 'A/E reviewer', 'text' ),
			f( 'lead_time_days', 'Lead time (days)', 'number' ),
			f( 'required_on_site', 'Required on site', 'date', { list: true } ),
			f( 'date_received', 'Date received', 'date' ),
			f( 'date_returned', 'Date returned', 'date', { list: true } ),
			f( 'reviewer_notes', 'Reviewer notes', 'textarea' ),
			f( 'file_url', 'Document link', 'url' ),
		],
	},
	{
		section: 'engineering', id: 'drawings', name: 'Drawings', icon: 'bi-bounding-box',
		// Revision-controlled drawing register: a new revision supersedes the prior.
		workflow: wf( {
			'Current': [
				{ to: 'Superseded', label: 'Supersede (new revision)', party: [ 'gc', 'consultant' ] },
				{ to: 'Void', label: 'Void', party: [ 'gc', 'consultant' ] },
			],
			'Superseded': [],
			'Void': [],
		} ),
		fields: [
			f( 'number', 'Drawing number', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'discipline', 'Discipline', 'select', { options: [ 'Architectural', 'Structural', 'Civil', 'Mechanical', 'Electrical', 'Plumbing', 'Fire Protection', 'Landscape', 'Other' ], list: true } ),
			f( 'set_type', 'Issued set', 'select', { options: [ 'Bid', 'Permit', 'Issued for Construction', 'Construction Documents', 'Addendum', 'ASI / Bulletin', 'As-Built' ], list: true } ),
			f( 'revision', 'Revision', 'text', { list: true } ),
			f( 'rev_date', 'Revision date', 'date', { list: true } ),
			f( 'file_url', 'Drawing link', 'url' ),
		],
	},
	{
		section: 'engineering', id: 'specifications', name: 'Specifications', icon: 'bi-card-checklist',
		workflow: wf( {
			'Current': [
				{ to: 'Superseded', label: 'Supersede (new revision)', party: [ 'gc', 'consultant' ] },
				{ to: 'Void', label: 'Void', party: [ 'gc', 'consultant' ] },
			],
			'Superseded': [],
			'Void': [],
		} ),
		fields: [
			f( 'section_no', 'Spec section', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'division', 'CSI division', 'select', { ...LOOKUP_CSI, list: true } ),
			f( 'revision', 'Revision', 'text', { list: true } ),
			f( 'issue_date', 'Issue date', 'date', { list: true } ),
			f( 'file_url', 'Document link', 'url' ),
		],
	},
	{
		section: 'engineering', id: 'file-explorer', name: 'File Explorer', icon: 'bi-folder2-open',
		statuses: [ 'Active', 'Archived' ],
		fields: [
			f( 'name', 'File name', 'text', { required: true, list: true } ),
			f( 'folder', 'Folder path', 'text', { required: true, list: true, help: 'e.g. /engineering/specs' } ),
			f( 'category', 'Category', 'select', { options: [ 'Document', 'Drawing', 'Photo', 'Spreadsheet', 'Model', 'Other' ], list: true } ),
			f( 'file_url', 'File link', 'url', { required: true } ),
			f( 'size_kb', 'Size (KB)', 'number' ),
			f( 'description', 'Description', 'textarea' ),
		],
	},
	{
		section: 'engineering', id: 'permitting', name: 'Permitting', icon: 'bi-patch-check',
		// Authority-having-jurisdiction permit lifecycle from application to close.
		workflow: wf( {
			'Not Applied': [ { to: 'Applied', label: 'Submit application', party: [ 'gc' ] } ],
			'Applied': [
				{ to: 'Under Review', label: 'In AHJ review', party: [ 'gc' ] },
				{ to: 'Issued', label: 'Issued', party: [ 'gc' ] },
			],
			'Under Review': [
				{ to: 'Issued', label: 'Issued', party: [ 'gc' ] },
				{ to: 'Applied', label: 'Resubmit (comments)', party: [ 'gc' ] },
			],
			'Issued': [
				{ to: 'Closed', label: 'Close / final', party: [ 'gc' ] },
				{ to: 'Expired', label: 'Expired', party: [ 'gc' ] },
			],
			'Expired': [],
			'Closed': [],
		} ),
		fields: [
			f( 'permit_no', 'Permit number', 'text', { list: true } ),
			f( 'title', 'Permit title', 'text', { required: true, list: true } ),
			f( 'permit_type', 'Type', 'select', { options: [ 'Building', 'Foundation', 'Electrical', 'Plumbing', 'Mechanical', 'Demolition', 'Grading', 'Right-of-Way', 'Occupancy', 'Other' ], list: true } ),
			f( 'authority', 'Issuing authority (AHJ)', 'text', { list: true } ),
			f( 'applied_date', 'Applied', 'date' ),
			f( 'issued_date', 'Issued', 'date', { list: true } ),
			f( 'expires', 'Expires', 'date', { list: true } ),
			f( 'fee', 'Fee', 'currency' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'engineering', id: 'meetings', name: 'Meeting Agenda / Minutes', icon: 'bi-calendar3',
		// Agenda → held → minutes issued; action items tracked in Action Items.
		workflow: wf( {
			'Scheduled': [ { to: 'Agenda Issued', label: 'Issue agenda', party: [ 'gc' ] } ],
			'Agenda Issued': [ { to: 'Held', label: 'Mark held', party: [ 'gc' ] } ],
			'Held': [ { to: 'Minutes Issued', label: 'Issue minutes', party: [ 'gc' ] } ],
			'Minutes Issued': [ { to: 'Held', label: 'Revise minutes', party: [ 'gc' ] } ],
		} ),
		relations: [
			{ spawn: 'action-items', label: 'Create action item', map: { title: 'title', source: 'title' } },
		],
		fields: [
			f( 'title', 'Meeting title', 'text', { required: true, list: true } ),
			f( 'meeting_no', 'Meeting number', 'text', { list: true } ),
			f( 'meeting_type', 'Type', 'select', { options: [ 'OAC', 'Subcontractor', 'Safety', 'Preconstruction', 'Coordination', 'Progress', 'Other' ], list: true } ),
			f( 'meeting_date', 'Date & time', 'datetime', { required: true, list: true } ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'attendees', 'Attendees', 'textarea' ),
			f( 'agenda', 'Agenda', 'textarea' ),
			f( 'minutes', 'Minutes', 'textarea' ),
			f( 'next_meeting', 'Next meeting', 'datetime' ),
		],
	},
	{
		section: 'engineering', id: 'transmittals', name: 'Transmittals', icon: 'bi-send',
		workflow: wf( {
			'Draft': [ { to: 'Sent', label: 'Send', party: [ 'gc', 'consultant' ] } ],
			'Sent': [ { to: 'Acknowledged', label: 'Acknowledge receipt', party: [ 'gc', 'owner', 'rep', 'consultant', 'subcontractor' ] } ],
			'Acknowledged': [],
		} ),
		fields: [
			f( 'number', 'Transmittal number', 'text', { required: true, list: true } ),
			f( 'to_company', 'To', 'text', { required: true, list: true } ),
			f( 'attention', 'Attention', 'text' ),
			f( 'purpose', 'Purpose', 'select', { options: [ 'For Approval', 'For Review & Comment', 'For Information', 'For Construction', 'As Requested', 'For Record' ], list: true } ),
			f( 'sent_date', 'Date sent', 'date', { list: true } ),
			f( 'via', 'Sent via', 'select', { options: [ 'Email', 'Courier', 'Mail', 'Hand Delivery', 'Portal' ], list: true } ),
			f( 'contents', 'Contents', 'textarea', { required: true } ),
			f( 'remarks', 'Remarks', 'textarea' ),
		],
	},

	/* ------------------------------------------------------------------ Field */
	{
		section: 'field', id: 'daily-reports', name: 'Daily Reports', icon: 'bi-sun',
		// Superintendent drafts and submits; PM reviews and approves (or returns).
		workflow: wf( {
			'Draft': [ { to: 'Submitted', label: 'Submit', party: [ 'gc', 'subcontractor' ] } ],
			'Submitted': [
				{ to: 'Approved', label: 'Approve', party: [ 'gc' ] },
				{ to: 'Draft', label: 'Return for revision', party: [ 'gc' ] },
			],
			'Approved': [],
		} ),
		fields: [
			f( 'report_date', 'Report date', 'date', { required: true, list: true } ),
			f( 'superintendent', 'Superintendent', 'text', { required: true, list: true } ),
			f( 'weather', 'Weather summary', 'text', { list: true, help: 'Use “Fetch site weather” to auto-fill.' } ),
			f( 'temp_high', 'High (°F)', 'number' ),
			f( 'temp_low', 'Low (°F)', 'number' ),
			f( 'precipitation', 'Precipitation (in)', 'number', { step: '0.01' } ),
			f( 'manpower', 'Total workers on site', 'number', { list: true } ),
			f( 'work_performed', 'Work performed', 'textarea', { required: true } ),
			f( 'deliveries', 'Deliveries received', 'textarea' ),
			f( 'equipment_on_site', 'Equipment on site', 'textarea' ),
			f( 'delays', 'Delays / issues', 'textarea' ),
			f( 'safety_notes', 'Safety observations', 'textarea' ),
			f( 'visitors', 'Visitors / inspections', 'textarea' ),
			f( 'superintendent_signature', 'Superintendent signature', 'signature' ),
		],
	},
	{
		section: 'field', id: 'photo-library', name: 'Photo Library', icon: 'bi-camera',
		statuses: [ 'Active', 'Archived' ],
		fields: [
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'photo_url', 'Photo link', 'url', { required: true } ),
			f( 'album', 'Album', 'text', { list: true } ),
			f( 'taken_on', 'Date taken', 'date', { list: true } ),
			f( 'location', 'Location', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'trade', 'Trade / scope', 'text', { list: true } ),
			f( 'tags', 'Tags', 'text', { help: 'Comma-separated' } ),
			f( 'description', 'Description', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'schedule', name: 'Schedule', icon: 'bi-calendar-range',
		statuses: [ 'Not Started', 'In Progress', 'Complete', 'On Hold' ],
		fields: [
			f( 'activity_id', 'Activity ID', 'text', { list: true } ),
			f( 'activity', 'Activity name', 'text', { required: true, list: true } ),
			f( 'start_date', 'Start', 'date', { required: true, list: true } ),
			f( 'finish_date', 'Finish', 'date', { required: true, list: true } ),
			f( 'percent_complete', '% complete', 'number', { list: true } ),
			f( 'responsible', 'Responsible party' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'checklists', name: 'Checklists', icon: 'bi-check2-square',
		// Issued → completed by the crew → reviewed; failed loops for rework.
		workflow: wf( {
			'Open': [ { to: 'In Progress', label: 'Start', party: [ 'gc', 'subcontractor' ] } ],
			'In Progress': [ { to: 'Ready for Review', label: 'Submit for review', party: [ 'gc', 'subcontractor' ] } ],
			'Ready for Review': [
				{ to: 'Complete', label: 'Pass / complete', party: [ 'gc', 'consultant' ] },
				{ to: 'Failed', label: 'Fail', party: [ 'gc', 'consultant' ] },
			],
			'Failed': [ { to: 'In Progress', label: 'Rework', party: [ 'gc', 'subcontractor' ] } ],
			'Complete': [],
		} ),
		fields: [
			f( 'title', 'Checklist title', 'text', { required: true, list: true } ),
			f( 'checklist_type', 'Type', 'select', { options: [ 'Quality', 'Safety', 'Pre-pour', 'MEP Rough-in', 'Inspection', 'Commissioning', 'Other' ], list: true } ),
			f( 'location', 'Location', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'responsible', 'Responsible', 'text', { list: true } ),
			f( 'due_date', 'Due', 'date', { list: true } ),
			f( 'items', 'Checklist items', 'textarea', { help: 'One item per line' } ),
			f( 'results', 'Results / notes', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'punchlist', name: 'Punch List', icon: 'bi-list-check',
		// Procore-style ball-in-court: GC raises → assigned to a trade → work done →
		// ready for review → final approver verifies (or rejects for rework).
		workflow: wf( {
			'Open': [ { to: 'Work Required', label: 'Assign to trade', party: [ 'gc' ] } ],
			'Work Required': [
				{ to: 'Ready for Review', label: 'Mark ready for review', party: [ 'subcontractor', 'gc' ] },
			],
			'Ready for Review': [
				{ to: 'Verified', label: 'Verify & close', party: [ 'gc', 'consultant' ] },
				{ to: 'Work Required', label: 'Reject — rework', party: [ 'gc', 'consultant' ] },
			],
			'Verified': [],
		} ),
		fields: [
			f( 'item_no', 'Item number', 'text', { list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'location', 'Location', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'responsible', 'Responsible subcontractor', 'text', { list: true } ),
			f( 'ball_in_court', 'Ball in court', 'text', { list: true } ),
			f( 'priority', 'Priority', 'select', { options: [ 'Low', 'Normal', 'High' ], list: true } ),
			f( 'due_date', 'Due', 'date', { list: true } ),
			f( 'backcharge', 'Backcharge amount', 'currency' ),
			f( 'photo_url', 'Photo link', 'url' ),
		],
	},
	{
		section: 'field', id: 'pull-planning', name: 'Pull Planning', icon: 'bi-arrow-left-right',
		statuses: [ 'Planned', 'Committed', 'Complete', 'Missed' ],
		fields: [
			f( 'milestone', 'Milestone', 'text', { required: true, list: true } ),
			f( 'task', 'Task / commitment', 'text', { required: true, list: true } ),
			f( 'trade', 'Trade', 'text', { list: true } ),
			f( 'promise_date', 'Promise date', 'date', { required: true, list: true } ),
			f( 'duration_days', 'Duration (days)', 'number' ),
			f( 'constraints', 'Constraints', 'textarea' ),
			f( 'variance_reason', 'Variance reason', 'textarea' ),
		],
	},

	/* ------------------------------------------------------------------ Safety */
	{
		section: 'safety', id: 'observations', name: 'Observations', icon: 'bi-eye',
		// Behavior-based safety: at-risk findings can spawn a tracked corrective action.
		workflow: wf( {
			'Open': [
				{ to: 'In Progress', label: 'Action in progress', party: [ 'gc', 'subcontractor' ] },
				{ to: 'Closed', label: 'Close (no action)', party: [ 'gc' ] },
			],
			'In Progress': [ { to: 'Closed', label: 'Close', party: [ 'gc' ] } ],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'action-items', label: 'Assign corrective action', map: { title: 'title', description: 'corrective_action' } },
		],
		fields: [
			f( 'title', 'Observation', 'text', { required: true, list: true } ),
			f( 'observation_type', 'Type', 'select', { options: [ 'Safe Behavior', 'At-Risk Behavior', 'Hazard', 'Near Miss', 'Good Catch' ], list: true } ),
			f( 'severity', 'Severity', 'select', { options: [ 'Low', 'Medium', 'High', 'Critical' ], list: true } ),
			f( 'observed_on', 'Date observed', 'date', { list: true } ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'company_observed', 'Company observed', 'text', { list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'corrective_action', 'Corrective action', 'textarea' ),
		],
	},
	{
		section: 'safety', id: 'pretask-plans', name: 'Pre-Task Plans (PTPs)', icon: 'bi-clipboard-check',
		statuses: [ 'Draft', 'Submitted', 'Reviewed' ],
		fields: [
			f( 'task', 'Task description', 'text', { required: true, list: true } ),
			f( 'plan_date', 'Date', 'date', { required: true, list: true } ),
			f( 'crew', 'Crew / company', 'text', { list: true } ),
			f( 'crew_size', 'Crew size', 'number', { list: true } ),
			f( 'location', 'Work location', 'select', LOOKUP_LOCATION ),
			f( 'hazards', 'Hazards identified', 'textarea', { required: true } ),
			f( 'controls', 'Controls / mitigation', 'textarea', { required: true } ),
			f( 'tools', 'Tools & equipment', 'textarea' ),
			f( 'foreman_signature', 'Foreman signature', 'signature' ),
		],
	},
	{
		section: 'safety', id: 'jhas', name: 'Job Hazard Analyses (JHAs)', icon: 'bi-exclamation-triangle',
		statuses: [ 'Draft', 'Active', 'Under Review', 'Archived' ],
		fields: [
			f( 'title', 'JHA title', 'text', { required: true, list: true } ),
			f( 'trade', 'Trade / operation', 'text', { list: true } ),
			f( 'revision', 'Revision', 'text', { list: true } ),
			f( 'review_date', 'Last review', 'date', { list: true } ),
			f( 'job_steps', 'Job steps', 'textarea', { required: true } ),
			f( 'hazards', 'Hazards', 'textarea', { required: true } ),
			f( 'controls', 'Controls', 'textarea', { required: true } ),
		],
	},
	{
		section: 'safety', id: 'orientations', name: 'Employee Orientations', icon: 'bi-person-badge',
		statuses: [ 'Scheduled', 'Completed', 'Expired' ],
		fields: [
			f( 'employee_name', 'Employee name', 'text', { required: true, list: true } ),
			f( 'company', 'Company', 'text', { required: true, list: true } ),
			f( 'trade', 'Trade', 'text', { list: true } ),
			f( 'orientation_date', 'Orientation date', 'date', { required: true, list: true } ),
			f( 'badge_no', 'Badge number', 'text', { list: true } ),
			f( 'emergency_contact', 'Emergency contact' ),
			f( 'certifications', 'Certifications', 'textarea' ),
			f( 'employee_signature', 'Employee signature', 'signature' ),
		],
	},

	/* ------------------------------------------------------------------ Contracts */
	{
		section: 'contracts', id: 'prime-contract', name: 'Prime Contract', icon: 'bi-award',
		statuses: [ 'Draft', 'Out for Signature', 'Executed', 'Closed' ],
		fields: [
			f( 'contract_no', 'Contract number', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'contract_type', 'Delivery type', 'select', { options: [ 'GMP', 'Cost Plus', 'Lump Sum', 'CMAR' ], required: true, list: true } ),
			f( 'owner', 'Owner', 'text', { list: true } ),
			f( 'original_value', 'Original value', 'currency', { list: true } ),
			f( 'executed_date', 'Executed', 'date' ),
			f( 'retainage_pct', 'Retainage %', 'number', { step: '0.1' } ),
			f( 'scope', 'Scope summary', 'textarea' ),
		],
	},
	{
		section: 'contracts', id: 'subcontracts', name: 'Subcontracts', icon: 'bi-diagram-3',
		statuses: [ 'Draft', 'Issued', 'Out for Signature', 'Executed', 'Closed' ],
		fields: [
			f( 'subcontract_no', 'Subcontract number', 'text', { required: true, list: true } ),
			f( 'subcontractor', 'Subcontractor', 'text', { required: true, list: true } ),
			f( 'csi_division', 'CSI division', 'select', LOOKUP_CSI ),
			f( 'scope_title', 'Scope', 'text', { list: true } ),
			f( 'value', 'Contract value', 'currency', { list: true } ),
			f( 'executed_date', 'Executed', 'date', { list: true } ),
			f( 'retainage_pct', 'Retainage %', 'number', { step: '0.1' } ),
			f( 'inclusions', 'Inclusions / exclusions', 'textarea' ),
		],
	},
	{
		section: 'contracts', id: 'psas', name: 'Professional Service Agreements', icon: 'bi-briefcase',
		statuses: [ 'Draft', 'Out for Signature', 'Executed', 'Closed' ],
		fields: [
			f( 'agreement_no', 'Agreement number', 'text', { required: true, list: true } ),
			f( 'consultant', 'Consultant', 'text', { required: true, list: true } ),
			f( 'service', 'Service description', 'text', { list: true } ),
			f( 'value', 'Agreement value', 'currency', { list: true } ),
			f( 'start_date', 'Start', 'date' ),
			f( 'end_date', 'End', 'date', { list: true } ),
			f( 'terms', 'Key terms', 'textarea' ),
		],
	},
	{
		section: 'contracts', id: 'lien-waivers', name: 'Lien Waivers', icon: 'bi-file-earmark-check',
		statuses: [ 'Requested', 'Received', 'Approved', 'Rejected' ],
		fields: [
			f( 'company', 'Company', 'text', { required: true, list: true } ),
			f( 'waiver_type', 'Waiver type', 'select', { options: [ 'Conditional Progress', 'Unconditional Progress', 'Conditional Final', 'Unconditional Final' ], required: true, list: true } ),
			f( 'through_date', 'Through date', 'date', { list: true } ),
			f( 'amount', 'Amount', 'currency', { list: true } ),
			f( 'received_date', 'Received', 'date', { list: true } ),
			f( 'file_url', 'Document link', 'url' ),
			f( 'authorized_signature', 'Authorized signature', 'signature' ),
		],
	},
	{
		section: 'contracts', id: 'insurance-certs', name: 'Certificates of Insurance', icon: 'bi-shield-lock',
		statuses: [ 'Requested', 'Received', 'Approved', 'Deficient', 'Expired' ],
		fields: [
			f( 'company', 'Company', 'text', { required: true, list: true } ),
			f( 'carrier', 'Carrier', 'text', { list: true } ),
			f( 'policy_no', 'Policy number', 'text', { list: true } ),
			f( 'coverage_type', 'Coverage', 'select', { options: [ 'GL', 'Auto', 'Umbrella', 'Workers Comp', 'Professional', 'Builders Risk' ], list: true } ),
			f( 'effective_date', 'Effective', 'date' ),
			f( 'expiration_date', 'Expires', 'date', { list: true } ),
			f( 'limit', 'Limit', 'currency' ),
			f( 'file_url', 'Certificate link', 'url' ),
		],
	},
	{
		section: 'contracts', id: 'letters-of-intent', name: 'Letters of Intent', icon: 'bi-envelope-paper',
		statuses: [ 'Draft', 'Issued', 'Accepted', 'Superseded' ],
		fields: [
			f( 'loi_no', 'LOI number', 'text', { required: true, list: true } ),
			f( 'company', 'Company', 'text', { required: true, list: true } ),
			f( 'scope', 'Scope', 'text', { list: true } ),
			f( 'not_to_exceed', 'Not-to-exceed amount', 'currency', { list: true } ),
			f( 'issued_date', 'Issued', 'date', { list: true } ),
			f( 'expiration_date', 'Expires', 'date' ),
			f( 'terms', 'Terms', 'textarea' ),
		],
	},

	/* ------------------------------------------------------------------ Cost */
	{
		section: 'cost', id: 'budget-forecast', name: 'Budget & Forecast', icon: 'bi-bar-chart-line',
		statuses: [ 'Original Budget', 'Revised', 'Forecast', 'Final' ],
		fields: [
			f( 'cost_code', 'Cost code', 'select', { ...LOOKUP_COSTCODE, required: true, list: true } ),
			f( 'description', 'Description', 'text', { required: true, list: true } ),
			f( 'original_budget', 'Original budget', 'currency', { list: true } ),
			f( 'approved_changes', 'Approved changes', 'currency' ),
			f( 'committed', 'Committed cost', 'currency', { list: true } ),
			f( 'forecast_final', 'Forecast at completion', 'currency', { list: true } ),
			f( 'variance_notes', 'Variance notes', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'invoicing', name: 'Invoicing (AIA G702/G703)', icon: 'bi-receipt',
		statuses: [ 'Draft', 'Submitted', 'Approved', 'Paid', 'Rejected' ],
		fields: [
			f( 'application_no', 'Application number', 'text', { required: true, list: true } ),
			f( 'period_to', 'Period to', 'date', { required: true, list: true } ),
			f( 'scheduled_value', 'Scheduled value (G703)', 'currency' ),
			f( 'work_completed', 'Work completed to date', 'currency', { list: true } ),
			f( 'stored_materials', 'Stored materials', 'currency' ),
			f( 'retainage', 'Retainage', 'currency' ),
			f( 'current_due', 'Current payment due (G702)', 'currency', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'direct-costs', name: 'Direct Costs', icon: 'bi-cash-stack',
		// Coded to a budget line; approved then paid (or disputed).
		workflow: wf( {
			'Pending': [
				{ to: 'Approved', label: 'Approve', party: [ 'gc' ] },
				{ to: 'Disputed', label: 'Dispute', party: [ 'gc' ] },
			],
			'Approved': [ { to: 'Paid', label: 'Mark paid', party: [ 'gc' ] } ],
			'Disputed': [ { to: 'Pending', label: 'Re-submit', party: [ 'gc', 'subcontractor' ] } ],
			'Paid': [],
		} ),
		fields: [
			f( 'cost_type', 'Type', 'select', { options: [ 'Invoice', 'Certified Payroll', 'Expense' ], required: true, list: true } ),
			f( 'vendor', 'Vendor / employee', 'text', { required: true, list: true } ),
			f( 'reference_no', 'Invoice / reference number', 'text', { list: true } ),
			f( 'cost_code', 'Cost code (budget line)', 'select', { ...LOOKUP_COSTCODE, list: true } ),
			f( 'cost_date', 'Date', 'date', { list: true } ),
			f( 'amount', 'Amount', 'currency', { required: true, list: true } ),
			f( 'description', 'Description', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'potential-changes', name: 'Potential Changes', icon: 'bi-lightning',
		// PCO pipeline: identified → priced → submitted → approved → becomes a CO.
		workflow: wf( {
			'Identified': [ { to: 'Pricing', label: 'Request pricing', party: [ 'gc' ] } ],
			'Pricing': [ { to: 'Submitted', label: 'Submit to owner', party: [ 'gc' ] } ],
			'Submitted': [
				{ to: 'Approved', label: 'Approve', party: [ 'owner', 'rep' ] },
				{ to: 'Rejected', label: 'Reject', party: [ 'owner', 'rep' ] },
			],
			'Approved': [ { to: 'Converted to CO', label: 'Convert to change order', party: [ 'gc' ] } ],
			'Rejected': [],
			'Converted to CO': [],
		} ),
		relations: [
			{ spawn: 'change-orders', label: 'Convert to Change Order', map: { title: 'title', amount: 'rom_estimate', description: 'description' } },
		],
		fields: [
			f( 'pco_no', 'PCO number', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'reason', 'Reason', 'select', { options: [ 'Owner Request', 'Design Change', 'Field Condition', 'Code Requirement', 'Allowance', 'Other' ], list: true } ),
			f( 'rom_estimate', 'ROM estimate', 'currency', { list: true } ),
			f( 'cost_code', 'Cost code (budget line)', 'select', { ...LOOKUP_COSTCODE, list: true } ),
			f( 'identified_date', 'Identified', 'date', { list: true } ),
			f( 'description', 'Description', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'change-orders', name: 'Change Orders', icon: 'bi-arrow-repeat',
		workflow: wf( {
			'Draft': [ { to: 'Submitted', label: 'Submit', party: [ 'gc' ] } ],
			'Submitted': [
				{ to: 'Approved', label: 'Approve', party: [ 'owner', 'rep' ] },
				{ to: 'Rejected', label: 'Reject', party: [ 'owner', 'rep' ] },
			],
			'Approved': [ { to: 'Executed', label: 'Execute', party: [ 'gc' ] } ],
			'Rejected': [],
			'Executed': [],
		} ),
		fields: [
			f( 'co_no', 'CO number', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'co_type', 'Type', 'select', { options: [ 'Owner CO', 'Subcontract CO' ], list: true } ),
			f( 'cost_code', 'Cost code (budget line)', 'select', { ...LOOKUP_COSTCODE, list: true } ),
			f( 'amount', 'Amount', 'currency', { required: true, list: true } ),
			f( 'time_extension_days', 'Time extension (days)', 'number' ),
			f( 'executed_date', 'Executed', 'date', { list: true } ),
			f( 'description', 'Description', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'approval-letters', name: 'Approval Letters & Directives', icon: 'bi-file-earmark-medical',
		workflow: wf( {
			'Draft': [ { to: 'Issued', label: 'Issue', party: [ 'gc' ] } ],
			'Issued': [
				{ to: 'Acknowledged', label: 'Acknowledge', party: [ 'owner', 'rep', 'subcontractor' ] },
				{ to: 'Superseded', label: 'Supersede', party: [ 'gc' ] },
			],
			'Acknowledged': [],
			'Superseded': [],
		} ),
		fields: [
			f( 'letter_no', 'Letter number', 'text', { required: true, list: true } ),
			f( 'letter_type', 'Type', 'select', { options: [ 'Approval Letter', 'Construction Change Directive', 'Field Directive', 'Notice to Proceed' ], required: true, list: true } ),
			f( 'subject', 'Subject', 'text', { required: true, list: true } ),
			f( 'issued_to', 'Issued to', 'text', { list: true } ),
			f( 'issued_date', 'Issued', 'date', { list: true } ),
			f( 'amount', 'Amount (if applicable)', 'currency' ),
			f( 'body', 'Letter body', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'tm-tickets', name: 'Time & Materials Tickets', icon: 'bi-stopwatch',
		workflow: wf( {
			'Open': [ { to: 'Submitted', label: 'Submit', party: [ 'subcontractor', 'gc' ] } ],
			'Submitted': [
				{ to: 'Approved', label: 'Approve', party: [ 'gc' ] },
				{ to: 'Rejected', label: 'Reject', party: [ 'gc' ] },
			],
			'Approved': [ { to: 'Invoiced', label: 'Mark invoiced', party: [ 'gc', 'subcontractor' ] } ],
			'Rejected': [ { to: 'Open', label: 'Revise', party: [ 'subcontractor', 'gc' ] } ],
			'Invoiced': [],
		} ),
		fields: [
			f( 'ticket_no', 'Ticket number', 'text', { required: true, list: true } ),
			f( 'work_date', 'Work date', 'date', { required: true, list: true } ),
			f( 'company', 'Company', 'text', { required: true, list: true } ),
			f( 'description', 'Work description', 'textarea', { required: true } ),
			f( 'labor_hours', 'Labor hours', 'number', { list: true } ),
			f( 'material_cost', 'Material cost', 'currency' ),
			f( 'equipment_cost', 'Equipment cost', 'currency' ),
			f( 'total', 'Ticket total', 'currency', { list: true } ),
			f( 'authorized_signature', 'Authorized by (signature)', 'signature' ),
		],
	},

	/* ------------------------------------------------------------------ BIM */
	{
		section: 'bim', id: 'bim-models', name: '3D Models', icon: 'bi-box',
		// Revision-controlled model register; a new version supersedes the prior.
		workflow: wf( {
			'Current': [
				{ to: 'Superseded', label: 'Supersede (new version)', party: [ 'gc', 'consultant' ] },
				{ to: 'Archived', label: 'Archive', party: [ 'gc' ] },
			],
			'Superseded': [ { to: 'Archived', label: 'Archive', party: [ 'gc' ] } ],
			'Archived': [],
		} ),
		fields: [
			f( 'title', 'Model title', 'text', { required: true, list: true } ),
			f( 'discipline', 'Discipline', 'select', { options: [ 'Architectural', 'Structural', 'MEP', 'Civil', 'Federated', 'Other' ], list: true } ),
			f( 'version', 'Version', 'text', { list: true } ),
			f( 'model_date', 'Model date', 'date', { list: true } ),
			f( 'ifc_url', 'IFC file URL', 'url', { required: true, help: 'Direct link to an .ifc file (e.g. a WordPress Media Library URL).' } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'bim', id: 'coordination-issues', name: 'Coordination Issues', icon: 'bi-exclamation-diamond',
		// Clash/coordination issue; if it needs design clarification it becomes an RFI.
		workflow: wf( {
			'Open': [ { to: 'Assigned', label: 'Assign', party: [ 'gc' ] } ],
			'Assigned': [
				{ to: 'Resolved', label: 'Resolve', party: [ 'gc', 'subcontractor', 'consultant' ] },
				{ to: 'Open', label: 'Reopen', party: [ 'gc' ] },
			],
			'Resolved': [ { to: 'Closed', label: 'Verify & close', party: [ 'gc' ] } ],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'rfis', label: 'Raise RFI (needs design input)', map: { subject: 'title', question: 'description', drawing_ref: 'location' } },
		],
		fields: [
			f( 'issue_no', 'Issue number', 'text', { list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'clash_type', 'Clash type', 'select', { options: [ 'Hard Clash', 'Clearance', 'Workflow', 'Design', 'Other' ], list: true } ),
			f( 'models_involved', 'Models / trades involved', 'text', { list: true } ),
			f( 'location', 'Location', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'priority', 'Priority', 'select', { options: [ 'Low', 'Medium', 'High', 'Critical' ], list: true } ),
			f( 'due_date', 'Due', 'date', { list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'resolution', 'Resolution', 'textarea' ),
		],
	},

	/* ------------------------------------------------------------------ Change Management
	   The patent's core: the integrated field change-order workflow. Records
	   chain together — PCO Request -> NOC -> Directive -> Proposal -> COR, and
	   Directive -> eTicket — each step gated by the acting party's role. */
	{
		section: 'change-management', id: 'pco-requests', name: 'PCO Requests (PCOR)', icon: 'bi-lightning-charge',
		// Field/GC-initiated potential change order request; GC reviews then issues a NOC.
		workflow: wf( {
			'Manager Review': [
				{ to: 'Ready to Submit', label: 'Approve for submission', party: [ 'gc' ] },
				{ to: 'Void', label: 'Void', party: [ 'gc' ] },
			],
			'Ready to Submit': [
				{ to: 'Issued as NOC', label: 'Issue NOC', party: [ 'gc' ] },
			],
			'Issued as NOC': [],
			'Void': [],
		} ),
		relations: [
			{ spawn: 'nocs', label: 'Issue NOC', map: { title: 'title', description: 'description', rom_estimate: 'rom_estimate', drawing_ref: 'drawing_ref' } },
		],
		fields: [
			f( 'pcor_no', 'PCOR number', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'origin', 'Originated by', 'select', { options: [ 'Field Staff (PCO Request)', 'Owner (Work Request)' ], list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'drawing_ref', 'Marked-up drawing link', 'url' ),
			f( 'photo_url', 'Photo link', 'url' ),
			f( 'rom_estimate', 'ROM estimate', 'currency', { list: true } ),
			f( 'cost_impact', 'Cost impact', 'checkbox' ),
			f( 'schedule_impact', 'Schedule impact', 'checkbox' ),
		],
	},
	{
		section: 'change-management', id: 'nocs', name: 'Notifications of Change (NOC)', icon: 'bi-bell',
		// GC issues to Owner/Rep; they return it with a direction (P&P / PO / DNP).
		workflow: wf( {
			'NOC Pending': [
				{ to: 'NOC Returned', label: 'Return NOC with direction', party: [ 'owner', 'rep' ], directions: [ 'P&P', 'PO', 'DNP' ] },
			],
			'NOC Returned': [
				{ to: 'Closed', label: 'Close', party: [ 'gc' ] },
			],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'directives', label: 'Issue PCO Directive', map: { title: 'title', scope: 'description', pco_number: 'pco_number', rom_estimate: 'rom_estimate' } },
		],
		fields: [
			f( 'noc_no', 'NOC number', 'text', { required: true, list: true } ),
			f( 'pco_number', 'PCO number', 'text', { list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'rom_estimate', 'ROM estimate', 'currency', { list: true } ),
			f( 'owner_comments', 'Owner comments', 'textarea' ),
		],
	},
	{
		section: 'change-management', id: 'ssi', name: 'Supplemental Scope Info (SSI)', icon: 'bi-file-earmark-plus',
		// Consultant/Rep/Owner-originated supplemental scope; routed to issuance as an SSID.
		workflow: wf( {
			'Owner Review': [
				{ to: 'Assign Contracts', label: 'Approve (recommend vs. execute)', party: [ 'owner', 'rep' ], directions: [ 'P&P', 'PO', 'DNP' ] },
			],
			'Assign Contracts': [
				{ to: 'Assign PCO & Funding', label: 'Assign contracts', party: [ 'gc' ] },
			],
			'Assign PCO & Funding': [
				{ to: 'Manager Review', label: 'Assign PCO# & funding', party: [ 'gc' ] },
			],
			'Manager Review': [
				{ to: 'SSI Approved', label: 'Approve', party: [ 'gc' ] },
			],
			'SSI Approved': [
				{ to: 'Issued as SSID', label: 'Issue SSID', party: [ 'gc' ] },
			],
			'Issued as SSID': [],
		} ),
		relations: [
			{ spawn: 'directives', label: 'Issue Scope Directive (SSID)', map: { title: 'title', scope: 'scope_description', pco_number: 'pco_number' } },
		],
		fields: [
			f( 'ssi_no', 'SSI number', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'scope_description', 'Scope description', 'textarea', { required: true } ),
			f( 'pco_number', 'PCO number', 'text', { list: true } ),
			f( 'funding_source', 'Funding source', 'text' ),
			f( 'backup_url', 'Backup / sketch link', 'url' ),
		],
	},
	{
		section: 'change-management', id: 'directives', name: 'Scope / PCO Directives', icon: 'bi-signpost-2',
		// GC issues to a subcontractor; PO -> proposal path, P&P -> ticket path.
		workflow: wf( {
			'Issued': [
				{ to: 'Acknowledged', label: 'Acknowledge', party: [ 'subcontractor' ] },
			],
			'Acknowledged': [
				{ to: 'In Progress', label: 'Start work', party: [ 'subcontractor' ] },
			],
			'In Progress': [
				{ to: 'Complete', label: 'Mark complete', party: [ 'subcontractor' ] },
			],
			'Complete': [
				{ to: 'Closed', label: 'Close', party: [ 'gc' ] },
			],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'proposals', label: 'Request proposal (Pricing Only)', map: { title: 'title', scope: 'scope', directive_no: 'directive_no' } },
			{ spawn: 'etickets', label: 'Generate eTicket (Proceed & Pricing)', map: { subcontractor: 'subcontractor', work_description: 'scope', directive_no: 'directive_no' } },
		],
		fields: [
			f( 'directive_no', 'Directive number', 'text', { required: true, list: true } ),
			f( 'directive_type', 'Type', 'select', { options: [ 'SSID (Scope)', 'PCOD (PCO)', 'Work Directive' ], required: true, list: true } ),
			f( 'subcontractor', 'Subcontractor', 'text', { required: true, list: true } ),
			f( 'pco_number', 'PCO number', 'text', { list: true } ),
			f( 'scope', 'Scope of work', 'textarea', { required: true } ),
			f( 'rom_estimate', 'ROM estimate', 'currency' ),
		],
	},
	{
		section: 'change-management', id: 'proposals', name: 'Subcontractor Proposals', icon: 'bi-cash-coin',
		// Sub submits pricing; GC reconciles; bundled into a COR/AL letter.
		workflow: wf( {
			'Submitted': [
				{ to: 'Under Reconciliation', label: 'Begin reconciliation', party: [ 'gc' ] },
			],
			'Under Reconciliation': [
				{ to: 'Reconciled', label: 'Mark reconciled', party: [ 'gc', 'subcontractor' ] },
				{ to: 'Submitted', label: 'Return to subcontractor', party: [ 'gc' ] },
			],
			'Reconciled': [
				{ to: 'Submitted as COR', label: 'Bundle into COR', party: [ 'gc' ] },
			],
			'Submitted as COR': [],
		} ),
		relations: [
			{ spawn: 'cors', label: 'Generate COR / AL letter', map: { title: 'title', amount: 'amount', scope: 'scope' } },
		],
		fields: [
			f( 'proposal_no', 'Proposal number', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'subcontractor', 'Subcontractor', 'text', { required: true, list: true } ),
			f( 'directive_no', 'Directive reference', 'text', { list: true } ),
			f( 'scope', 'Scope', 'textarea' ),
			f( 'amount', 'Proposed amount', 'currency', { required: true, list: true } ),
			f( 'breakdown', 'Cost breakdown', 'textarea' ),
		],
	},
	{
		section: 'change-management', id: 'cors', name: 'Change Order Requests (COR/AL)', icon: 'bi-envelope-paper-heart',
		// Reviewed/commented/approved within the eManager forum; posted to the owner.
		workflow: wf( {
			'Draft': [
				{ to: 'Submitted to Owner', label: 'Submit to owner', party: [ 'gc' ] },
			],
			'Submitted to Owner': [
				{ to: 'Approved', label: 'Approve', party: [ 'owner', 'rep' ] },
				{ to: 'Revise', label: 'Return for revision', party: [ 'owner', 'rep' ] },
			],
			'Revise': [
				{ to: 'Submitted to Owner', label: 'Resubmit', party: [ 'gc' ] },
			],
			'Approved': [
				{ to: 'Executed', label: 'Mark executed', party: [ 'gc' ] },
			],
			'Executed': [],
		} ),
		fields: [
			f( 'cor_no', 'COR / AL number', 'text', { required: true, list: true } ),
			f( 'letter_type', 'Letter type', 'select', { options: [ 'COR (Change Order Request)', 'AL (Approval Letter)' ], required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'amount', 'Amount', 'currency', { required: true, list: true } ),
			f( 'scope', 'Scope summary', 'textarea' ),
			f( 'owner_comments', 'Owner comments', 'textarea' ),
			f( 'cover_url', 'Cover letter link', 'url' ),
		],
	},
	{
		section: 'change-management', id: 'etickets', name: 'eTickets (T&M)', icon: 'bi-ticket-detailed',
		// Sub builds to their own labor types/rates; signed by Superintendent then GC.
		workflow: wf( {
			'Superintendent Review': [
				{ to: 'Manager Review', label: 'Sign (Superintendent)', party: [ 'gc' ] },
				{ to: 'Void', label: 'Void', party: [ 'gc' ] },
			],
			'Manager Review': [
				{ to: 'Approved', label: 'Approve & sign', party: [ 'gc' ] },
				{ to: 'Superintendent Review', label: 'Return', party: [ 'gc' ] },
			],
			'Approved': [
				{ to: 'Invoiced', label: 'Mark invoiced', party: [ 'gc', 'subcontractor' ] },
			],
			'Invoiced': [],
			'Void': [],
		} ),
		fields: [
			f( 'ticket_no', 'Ticket number', 'text', { required: true, list: true } ),
			f( 'work_date', 'Work date', 'date', { required: true, list: true } ),
			f( 'subcontractor', 'Subcontractor', 'text', { required: true, list: true } ),
			f( 'directive_no', 'Directive reference', 'text', { list: true } ),
			f( 'responsible_person', 'Responsible person', 'text' ),
			f( 'work_description', 'Work performed', 'textarea', { required: true } ),
			f( 'line_items', 'Labor / material / equipment lines', 'json', { help: 'Built on the eTicket form from the project rate tables.' } ),
			f( 'labor_total', 'Labor total', 'currency' ),
			f( 'material_total', 'Material total', 'currency' ),
			f( 'equipment_total', 'Equipment total', 'currency' ),
			f( 'total', 'Ticket total', 'currency', { list: true } ),
			f( 'super_signature', 'Superintendent signature', 'signature' ),
			f( 'gc_signature', 'GC signature', 'signature' ),
		],
	},
	{
		section: 'change-management', id: 'dcrs', name: 'Daily Construction Reports (DCR)', icon: 'bi-clipboard-data',
		// Sub completes digitally in the field; GC reviews and approves.
		workflow: wf( {
			'Superintendent Review': [
				{ to: 'Approved', label: 'Approve', party: [ 'gc' ] },
				{ to: 'Revise', label: 'Return for revision', party: [ 'gc' ] },
			],
			'Revise': [
				{ to: 'Superintendent Review', label: 'Resubmit', party: [ 'subcontractor' ] },
			],
			'Approved': [],
		} ),
		fields: [
			f( 'dcr_no', 'DCR number', 'text', { required: true, list: true } ),
			f( 'report_date', 'Report date', 'date', { required: true, list: true } ),
			f( 'subcontractor', 'Subcontractor', 'text', { required: true, list: true } ),
			f( 'work_performed', 'Work performed', 'textarea', { required: true } ),
			f( 'manpower', 'Workers on site', 'number', { list: true } ),
			f( 'equipment_used', 'Equipment used', 'textarea' ),
			f( 'delays', 'Delays / issues', 'textarea' ),
			f( 'sub_signature', 'Subcontractor signature', 'signature' ),
		],
	},

	/* ------------------------------------------------------------------ Closeout */
	{
		section: 'closeout', id: 'om-manuals', name: 'O&M Manuals', icon: 'bi-book',
		// Ball-in-court closeout submittal: sub provides → GC reviews → owner accepts.
		workflow: wf( {
			'Requested': [ { to: 'Received', label: 'Mark received', party: [ 'subcontractor', 'gc' ] } ],
			'Received': [ { to: 'Under Review', label: 'Begin review', party: [ 'gc' ] } ],
			'Under Review': [
				{ to: 'Accepted', label: 'Accept', party: [ 'gc', 'consultant' ] },
				{ to: 'Received', label: 'Return to sub', party: [ 'gc' ] },
			],
			'Accepted': [ { to: 'Transmitted to Owner', label: 'Transmit to owner', party: [ 'gc' ] } ],
			'Transmitted to Owner': [],
		} ),
		fields: [
			f( 'title', 'Manual title', 'text', { required: true, list: true } ),
			f( 'spec_section', 'Spec section', 'text', { list: true } ),
			f( 'subcontractor', 'Responsible subcontractor', 'text', { list: true } ),
			f( 'received_date', 'Received', 'date', { list: true } ),
			f( 'file_url', 'Document link', 'url' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'closeout', id: 'warranties', name: 'Warranties', icon: 'bi-patch-check-fill',
		// Warranty register; period typically starts at substantial completion.
		workflow: wf( {
			'Requested': [ { to: 'Received', label: 'Received', party: [ 'subcontractor', 'gc' ] } ],
			'Received': [ { to: 'Active', label: 'Activate (period starts)', party: [ 'gc' ] } ],
			'Active': [ { to: 'Expired', label: 'Expire', party: [ 'gc' ] } ],
			'Expired': [],
		} ),
		fields: [
			f( 'item', 'Warranted item / system', 'text', { required: true, list: true } ),
			f( 'company', 'Warrantor', 'text', { required: true, list: true } ),
			f( 'duration', 'Duration', 'text', { list: true, help: 'e.g. 2 years' } ),
			f( 'start_date', 'Warranty start', 'date', { list: true } ),
			f( 'end_date', 'Warranty end', 'date', { list: true } ),
			f( 'file_url', 'Document link', 'url' ),
			f( 'terms', 'Terms', 'textarea' ),
		],
	},
	{
		section: 'closeout', id: 'attic-stock', name: 'Attic Stock', icon: 'bi-archive',
		workflow: wf( {
			'Required': [ { to: 'Received', label: 'Received', party: [ 'subcontractor', 'gc' ] } ],
			'Received': [ { to: 'Stored', label: 'Stored on site', party: [ 'gc' ] } ],
			'Stored': [ { to: 'Turned Over', label: 'Turn over to owner', party: [ 'gc', 'owner', 'rep' ] } ],
			'Turned Over': [],
		} ),
		fields: [
			f( 'item', 'Item', 'text', { required: true, list: true } ),
			f( 'spec_section', 'Spec section', 'text', { list: true } ),
			f( 'quantity', 'Quantity', 'text', { required: true, list: true } ),
			f( 'storage_location', 'Storage location', 'text', { list: true } ),
			f( 'received_date', 'Received', 'date', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},

	/* ------------------------------------------------------------------ Resources (relational lookups) */
	{
		section: 'resources', id: 'locations', name: 'Locations', icon: 'bi-geo-alt',
		workflow: WF_ACTIVE,
		fields: [
			f( 'name', 'Location name', 'text', { required: true, list: true } ),
			f( 'area', 'Area / zone', 'text', { list: true } ),
			f( 'level', 'Level / floor', 'text', { list: true } ),
			f( 'description', 'Description', 'textarea' ),
		],
	},
	{
		section: 'resources', id: 'csi-divisions', name: 'CSI Divisions', icon: 'bi-list-ol',
		workflow: WF_ACTIVE,
		fields: [
			f( 'number', 'Division number', 'text', { required: true, list: true } ),
			f( 'title', 'Division title', 'text', { required: true, list: true } ),
			f( 'description', 'Description', 'textarea' ),
		],
	},
	{
		section: 'resources', id: 'cost-codes', name: 'Cost Codes', icon: 'bi-upc',
		workflow: WF_ACTIVE,
		fields: [
			f( 'code', 'Cost code', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'division', 'CSI division', 'select', { ...LOOKUP_CSI, list: true } ),
			f( 'category', 'Category', 'select', { options: [ 'Labor', 'Material', 'Equipment', 'Subcontract', 'General Conditions', 'Other' ], list: true } ),
		],
	},
	{
		section: 'resources', id: 'labor-rates', name: 'Labor Rates', icon: 'bi-person-workspace',
		workflow: WF_RATE,
		fields: [
			f( 'classification', 'Classification', 'text', { required: true, list: true } ),
			f( 'trade', 'Trade', 'text', { list: true } ),
			f( 'base_rate', 'Base rate ($/hr)', 'currency', { required: true, list: true } ),
			f( 'burden_rate', 'Burdened rate ($/hr)', 'currency', { list: true } ),
			f( 'overtime_rate', 'OT rate ($/hr)', 'currency' ),
			f( 'effective_date', 'Effective', 'date', { list: true } ),
		],
	},
	{
		section: 'resources', id: 'material-rates', name: 'Material Rates', icon: 'bi-bricks',
		workflow: WF_RATE,
		fields: [
			f( 'material', 'Material', 'text', { required: true, list: true } ),
			f( 'unit', 'Unit', 'text', { required: true, list: true, help: 'e.g. CY, SF, EA' } ),
			f( 'unit_cost', 'Unit cost', 'currency', { required: true, list: true } ),
			f( 'supplier', 'Supplier', 'text', { list: true } ),
			f( 'effective_date', 'Effective', 'date', { list: true } ),
		],
	},
	{
		section: 'resources', id: 'equipment-rates', name: 'Equipment Rates', icon: 'bi-truck',
		workflow: WF_RATE,
		fields: [
			f( 'equipment', 'Equipment', 'text', { required: true, list: true } ),
			f( 'rate_basis', 'Rate basis', 'select', { options: [ 'Hourly', 'Daily', 'Weekly', 'Monthly' ], required: true, list: true } ),
			f( 'rate', 'Rate', 'currency', { required: true, list: true } ),
			f( 'operated', 'Operated (with operator)', 'checkbox' ),
			f( 'vendor', 'Vendor', 'text', { list: true } ),
			f( 'effective_date', 'Effective', 'date' ),
		],
	},

	/* ==================================================================
	   COMPETITOR-PARITY EXPANSION (Procore / Autodesk Build / Trimble e-Builder)
	   Covers the full project lifecycle: prequal & bidding, design/PM,
	   quality, safety, field productivity, financials, sustainability and
	   handover. Modules carry their own `section` so they slot into the
	   sidebar; new sections (quality, sustainability) are in sections.json.
	   ================================================================== */

	/* -------- Preconstruction / Procurement -------- */
	{
		section: 'preconstruction', id: 'prequalification', name: 'Prequalification', icon: 'bi-clipboard-check',
		workflow: wf( {
			'Invited': [ { to: 'Submitted', label: 'Mark submitted', party: [ 'subcontractor' ] } ],
			'Submitted': [ { to: 'Under Review', label: 'Begin review', party: [ 'gc' ] } ],
			'Under Review': [
				{ to: 'Prequalified', label: 'Prequalify', party: [ 'gc' ] },
				{ to: 'Not Prequalified', label: 'Decline', party: [ 'gc' ] },
			],
			'Prequalified': [ { to: 'Expired', label: 'Expire', party: [ 'gc' ] } ],
			'Not Prequalified': [],
			'Expired': [],
		} ),
		fields: [
			f( 'company_name', 'Company', 'text', { required: true, list: true } ),
			f( 'trade', 'Trade', 'select', { ...LOOKUP_CSI, list: true } ),
			f( 'contact_name', 'Contact', 'text' ),
			f( 'emr', 'EMR rating', 'number', { step: '0.01' } ),
			f( 'annual_revenue', 'Annual revenue', 'currency' ),
			f( 'bonding_capacity', 'Bonding capacity', 'currency', { list: true } ),
			f( 'single_project_limit', 'Single-project limit', 'currency' ),
			f( 'safety_score', 'Safety score', 'number', { list: true } ),
			f( 'financial_score', 'Financial score', 'number' ),
			f( 'overall_score', 'Overall score', 'number', { list: true } ),
			f( 'expiration_date', 'Expires', 'date', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'preconstruction', id: 'bid-solicitations', name: 'Bid Solicitations (ITB)', icon: 'bi-megaphone',
		// Invitation to bid → collect submissions → evaluate → award.
		workflow: wf( {
			'Draft': [ { to: 'Issued', label: 'Issue ITB', party: [ 'gc' ] } ],
			'Issued': [ { to: 'Responses Due', label: 'Close to responses', party: [ 'gc' ] } ],
			'Responses Due': [ { to: 'Under Evaluation', label: 'Begin evaluation', party: [ 'gc' ] } ],
			'Under Evaluation': [
				{ to: 'Awarded', label: 'Award', party: [ 'gc' ] },
				{ to: 'Cancelled', label: 'Cancel', party: [ 'gc' ] },
			],
			'Awarded': [],
			'Cancelled': [],
		} ),
		relations: [
			{ spawn: 'bid-submissions', label: 'Add bidder', map: { bid_package: 'bid_package' } },
		],
		fields: [
			f( 'solicitation_no', 'Solicitation #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'bid_package', 'Bid package', 'text', { list: true } ),
			f( 'trade', 'Trade / scope', 'select', LOOKUP_CSI ),
			f( 'issue_date', 'Issued', 'date', { list: true } ),
			f( 'walkthrough_date', 'Pre-bid walkthrough', 'datetime' ),
			f( 'due_date', 'Responses due', 'date', { list: true } ),
			f( 'estimate', 'Engineer estimate', 'currency', { list: true } ),
			f( 'scope', 'Scope of work', 'textarea' ),
		],
	},
	{
		section: 'preconstruction', id: 'bid-submissions', name: 'Bid Submissions (Leveling)', icon: 'bi-bar-chart-steps',
		// Leveling pipeline; the awarded bid converts to a subcontract commitment.
		workflow: wf( {
			'Invited': [
				{ to: 'Submitted', label: 'Bid received', party: [ 'gc', 'subcontractor' ] },
				{ to: 'Declined', label: 'No bid', party: [ 'gc', 'subcontractor' ] },
			],
			'Submitted': [
				{ to: 'Shortlisted', label: 'Shortlist', party: [ 'gc' ] },
				{ to: 'Not Awarded', label: 'Not awarded', party: [ 'gc' ] },
			],
			'Shortlisted': [
				{ to: 'Awarded', label: 'Award', party: [ 'gc' ] },
				{ to: 'Not Awarded', label: 'Not awarded', party: [ 'gc' ] },
			],
			'Awarded': [],
			'Not Awarded': [],
			'Declined': [],
		} ),
		relations: [
			{ spawn: 'commitments', label: 'Award → create subcontract', map: { vendor: 'bidder', value: 'base_bid', title: 'bid_package' } },
		],
		fields: [
			f( 'bidder', 'Bidder', 'text', { required: true, list: true } ),
			f( 'bid_package', 'Bid package', 'text', { required: true, list: true } ),
			f( 'base_bid', 'Base bid', 'currency', { list: true } ),
			f( 'alternates_total', 'Alternates total', 'currency', { list: true } ),
			f( 'submitted_date', 'Submitted', 'date', { list: true } ),
			f( 'inclusions', 'Inclusions', 'textarea' ),
			f( 'exclusions', 'Exclusions / qualifications', 'textarea' ),
			f( 'score', 'Evaluation score', 'number', { list: true } ),
		],
	},
	{
		section: 'preconstruction', id: 'estimates', name: 'Estimates', icon: 'bi-calculator',
		// Estimate matures by phase; the final/GMP estimate seeds the budget.
		workflow: wf( {
			'Conceptual': [ { to: 'Schematic', label: 'Advance to SD', party: [ 'gc' ] } ],
			'Schematic': [ { to: 'Design Development', label: 'Advance to DD', party: [ 'gc' ] } ],
			'Design Development': [ { to: 'GMP', label: 'Advance to GMP', party: [ 'gc' ] } ],
			'GMP': [ { to: 'Final', label: 'Finalize', party: [ 'gc' ] } ],
			'Final': [ { to: 'Superseded', label: 'Supersede', party: [ 'gc' ] } ],
			'Superseded': [],
		} ),
		relations: [
			{ spawn: 'budget-forecast', label: 'Seed budget line', map: { description: 'title', cost_code: 'csi_division', original_budget: 'total' } },
		],
		fields: [
			f( 'estimate_no', 'Estimate #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'csi_division', 'CSI division', 'select', { ...LOOKUP_CSI, list: true } ),
			f( 'quantity', 'Quantity', 'number' ),
			f( 'unit', 'Unit', 'text' ),
			f( 'unit_cost', 'Unit cost', 'currency' ),
			f( 'total', 'Total', 'currency', { list: true } ),
			f( 'basis', 'Basis / assumptions', 'textarea' ),
		],
	},
	{
		section: 'preconstruction', id: 'value-engineering', name: 'Value Engineering', icon: 'bi-piggy-bank',
		workflow: wf( {
			'Proposed': [
				{ to: 'Under Review', label: 'Review', party: [ 'gc' ] },
				{ to: 'Rejected', label: 'Reject', party: [ 'gc' ] },
			],
			'Under Review': [
				{ to: 'Accepted', label: 'Accept', party: [ 'owner', 'rep' ] },
				{ to: 'Rejected', label: 'Reject', party: [ 'owner', 'rep' ] },
			],
			'Accepted': [ { to: 'Implemented', label: 'Mark implemented', party: [ 'gc' ] } ],
			'Rejected': [],
			'Implemented': [],
		} ),
		fields: [
			f( 've_no', 'VE #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'proposed_savings', 'Proposed savings', 'currency', { list: true } ),
			f( 'schedule_impact', 'Schedule impact', 'text' ),
			f( 'quality_impact', 'Quality / performance impact', 'textarea' ),
		],
	},

	/* -------- Engineering / Design management -------- */
	{
		section: 'engineering', id: 'issues', name: 'Issues', icon: 'bi-exclamation-circle',
		workflow: wf( {
			'Open': [ { to: 'Assigned', label: 'Assign', party: [ 'gc' ] } ],
			'Assigned': [ { to: 'In Review', label: 'Submit for review', party: [ 'gc', 'subcontractor' ] } ],
			'In Review': [
				{ to: 'Resolved', label: 'Resolve', party: [ 'gc' ] },
				{ to: 'Assigned', label: 'Reopen', party: [ 'gc' ] },
			],
			'Resolved': [ { to: 'Closed', label: 'Close', party: [ 'gc' ] } ],
			'Closed': [],
		} ),
		fields: [
			f( 'issue_no', 'Issue #', 'text', { list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'issue_type', 'Type', 'select', { options: [ 'Design', 'Field', 'Quality', 'Safety', 'Coordination', 'Other' ], list: true } ),
			f( 'priority', 'Priority', 'select', { options: [ 'Low', 'Medium', 'High', 'Critical' ], list: true } ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'assigned_to', 'Assigned to', 'text', { list: true } ),
			f( 'due_date', 'Due', 'date', { list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'resolution', 'Resolution', 'textarea' ),
		],
	},
	{
		section: 'engineering', id: 'action-items', name: 'Action Items', icon: 'bi-check2-circle',
		workflow: wf( {
			'Open': [ { to: 'In Progress', label: 'Start', party: [ 'gc', 'subcontractor', 'consultant' ] } ],
			'In Progress': [
				{ to: 'Complete', label: 'Complete', party: [ 'gc', 'subcontractor', 'consultant' ] },
				{ to: 'Blocked', label: 'Block', party: [ 'gc', 'subcontractor', 'consultant' ] },
			],
			'Blocked': [ { to: 'In Progress', label: 'Unblock', party: [ 'gc' ] } ],
			'Complete': [],
		} ),
		fields: [
			f( 'title', 'Action', 'text', { required: true, list: true } ),
			f( 'assigned_to', 'Assigned to', 'text', { list: true } ),
			f( 'priority', 'Priority', 'select', { options: [ 'Low', 'Medium', 'High' ], list: true } ),
			f( 'category', 'Category', 'select', { options: [ 'Design', 'Procurement', 'Field', 'Cost', 'Safety', 'Closeout', 'Other' ], list: true } ),
			f( 'due_date', 'Due', 'date', { list: true } ),
			f( 'source', 'Source (meeting / RFI / etc.)', 'text' ),
			f( 'description', 'Details', 'textarea' ),
		],
	},
	{
		section: 'engineering', id: 'correspondence', name: 'Correspondence', icon: 'bi-mailbox',
		statuses: [ 'Draft', 'Sent', 'Received', 'Actioned', 'Closed' ],
		fields: [
			f( 'ref_no', 'Reference #', 'text', { required: true, list: true } ),
			f( 'subject', 'Subject', 'text', { required: true, list: true } ),
			f( 'corr_type', 'Type', 'select', { options: [ 'Letter', 'Notice', 'Email', 'Memo', 'Directive', 'Claim' ], list: true } ),
			f( 'flow', 'Direction', 'select', { options: [ 'Incoming', 'Outgoing' ], list: true } ),
			f( 'from_party', 'From', 'text', { list: true } ),
			f( 'to_party', 'To', 'text', { list: true } ),
			f( 'date_sent', 'Date', 'date', { list: true } ),
			f( 'body', 'Body / summary', 'textarea' ),
			f( 'file_url', 'Attachment link', 'url' ),
		],
	},
	{
		section: 'engineering', id: 'design-reviews', name: 'Design Reviews', icon: 'bi-vector-pen',
		workflow: wf( {
			'Not Started': [ { to: 'In Review', label: 'Begin review', party: [ 'consultant', 'gc' ] } ],
			'In Review': [ { to: 'Comments Issued', label: 'Issue comments', party: [ 'consultant', 'gc' ] } ],
			'Comments Issued': [
				{ to: 'Approved', label: 'Approve', party: [ 'consultant', 'owner', 'rep' ] },
				{ to: 'In Review', label: 'Re-review', party: [ 'consultant', 'gc' ] },
			],
			'Approved': [],
		} ),
		fields: [
			f( 'review_no', 'Review #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'discipline', 'Discipline', 'select', { options: [ 'Architectural', 'Structural', 'Civil', 'MEP', 'Fire', 'Other' ], list: true } ),
			f( 'phase', 'Phase', 'select', { options: [ 'Schematic', 'Design Development', 'Construction Docs', 'Shop Drawings' ], list: true } ),
			f( 'reviewer', 'Reviewer', 'text', { list: true } ),
			f( 'review_date', 'Review date', 'date', { list: true } ),
			f( 'comments', 'Comments', 'textarea' ),
			f( 'file_url', 'Marked-up set link', 'url' ),
		],
	},

	/* -------- Quality (new section) -------- */
	{
		section: 'quality', id: 'inspections', name: 'Inspections', icon: 'bi-clipboard2-check',
		workflow: wf( {
			'Scheduled': [ { to: 'In Progress', label: 'Begin inspection', party: [ 'gc', 'consultant' ] } ],
			'In Progress': [
				{ to: 'Passed', label: 'Pass', party: [ 'gc', 'consultant' ] },
				{ to: 'Failed', label: 'Fail', party: [ 'gc', 'consultant' ] },
			],
			'Failed': [ { to: 'Reinspection', label: 'Schedule re-inspection', party: [ 'gc' ] } ],
			'Reinspection': [
				{ to: 'Passed', label: 'Pass', party: [ 'gc', 'consultant' ] },
				{ to: 'Failed', label: 'Fail', party: [ 'gc', 'consultant' ] },
			],
			'Passed': [ { to: 'Closed', label: 'Close', party: [ 'gc' ] } ],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'deficiencies', label: 'Log deficiency from finding', map: { title: 'title', description: 'findings', location: 'location' } },
			{ spawn: 'non-conformance', label: 'Raise NCR', map: { title: 'title', description: 'findings', location: 'location' } },
		],
		fields: [
			f( 'inspection_no', 'Inspection #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'inspection_type', 'Type', 'select', { options: [ 'Quality', 'Owner', 'AHJ / Code', 'Commissioning', 'Pre-pour', 'MEP Rough-in', 'Final', 'Other' ], list: true } ),
			f( 'location', 'Location', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'inspector', 'Inspector', 'text', { list: true } ),
			f( 'trade', 'Trade / scope', 'text', { list: true } ),
			f( 'scheduled_date', 'Scheduled', 'date', { list: true } ),
			f( 'completed_date', 'Completed', 'date', { list: true } ),
			f( 'checklist', 'Checklist items', 'textarea' ),
			f( 'findings', 'Findings / deficiencies', 'textarea' ),
			f( 'inspector_signature', 'Inspector signature', 'signature' ),
		],
	},
	{
		section: 'quality', id: 'non-conformance', name: 'Non-Conformance Reports', icon: 'bi-x-octagon',
		// QA assesses & dispositions (Rework/Repair/Use-as-is/Scrap) → corrective &
		// preventive action → verification before close (per ISO/QMS practice).
		workflow: wf( {
			'Open': [
				{ to: 'Dispositioned', label: 'Disposition', party: [ 'gc', 'consultant' ], directions: [ 'Rework', 'Repair', 'Use-as-is', 'Scrap' ] },
			],
			'Dispositioned': [ { to: 'Corrective Action', label: 'Assign corrective action', party: [ 'gc' ] } ],
			'Corrective Action': [ { to: 'Verification', label: 'Submit for verification', party: [ 'gc', 'subcontractor' ] } ],
			'Verification': [
				{ to: 'Closed', label: 'Verify & close', party: [ 'gc', 'consultant' ] },
				{ to: 'Corrective Action', label: 'Reject — redo', party: [ 'gc', 'consultant' ] },
			],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'action-items', label: 'Assign corrective action', map: { title: 'title', description: 'corrective_action' } },
		],
		fields: [
			f( 'ncr_no', 'NCR #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'spec_section', 'Spec section', 'text', { list: true } ),
			f( 'location', 'Location', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'responsible', 'Responsible party', 'text', { list: true } ),
			f( 'severity', 'Severity', 'select', { options: [ 'Minor', 'Major', 'Critical' ], list: true } ),
			f( 'description', 'Description of non-conformance', 'textarea', { required: true } ),
			f( 'root_cause', 'Root cause analysis', 'textarea' ),
			f( 'corrective_action', 'Corrective action', 'textarea' ),
			f( 'preventive_action', 'Preventive action', 'textarea' ),
			f( 'due_date', 'Due', 'date', { list: true } ),
		],
	},
	{
		section: 'quality', id: 'deficiencies', name: 'Deficiencies / Defects', icon: 'bi-tools',
		// Ball-in-court rework loop, like the punch list.
		workflow: wf( {
			'Open': [ { to: 'Work Required', label: 'Assign to trade', party: [ 'gc' ] } ],
			'Work Required': [ { to: 'Ready for Review', label: 'Mark ready', party: [ 'subcontractor', 'gc' ] } ],
			'Ready for Review': [
				{ to: 'Closed', label: 'Verify & close', party: [ 'gc', 'consultant' ] },
				{ to: 'Work Required', label: 'Reject — rework', party: [ 'gc', 'consultant' ] },
			],
			'Closed': [],
		} ),
		fields: [
			f( 'defect_no', 'Defect #', 'text', { list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'location', 'Location', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'trade', 'Trade', 'text', { list: true } ),
			f( 'ball_in_court', 'Ball in court', 'text', { list: true } ),
			f( 'severity', 'Severity', 'select', { options: [ 'Minor', 'Major', 'Critical' ], list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'due_date', 'Due', 'date', { list: true } ),
			f( 'photo_url', 'Photo link', 'url' ),
		],
	},
	{
		section: 'quality', id: 'test-records', name: 'Test Records', icon: 'bi-thermometer-half',
		workflow: wf( {
			'Scheduled': [
				{ to: 'Passed', label: 'Record pass', party: [ 'gc', 'consultant' ] },
				{ to: 'Failed', label: 'Record fail', party: [ 'gc', 'consultant' ] },
			],
			'Failed': [ { to: 'Retest', label: 'Schedule retest', party: [ 'gc' ] } ],
			'Retest': [
				{ to: 'Passed', label: 'Record pass', party: [ 'gc', 'consultant' ] },
				{ to: 'Failed', label: 'Record fail', party: [ 'gc', 'consultant' ] },
			],
			'Passed': [],
		} ),
		fields: [
			f( 'test_no', 'Test #', 'text', { required: true, list: true } ),
			f( 'test_type', 'Type', 'select', { options: [ 'Concrete', 'Soil / Compaction', 'Weld', 'Pressure', 'Electrical', 'Air Balance', 'Other' ], list: true } ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'test_date', 'Test date', 'date', { list: true } ),
			f( 'reading_value', 'Measured value', 'text' ),
			f( 'spec_requirement', 'Spec requirement', 'text' ),
			f( 'lab', 'Testing agency', 'text', { list: true } ),
			f( 'file_url', 'Report link', 'url' ),
		],
	},

	/* -------- Safety (expand) -------- */
	{
		section: 'safety', id: 'incidents', name: 'Incidents', icon: 'bi-bandaid',
		// OSHA-style investigation: report → investigate (root cause) → corrective
		// action → close, with recordability and lost/restricted-day capture.
		workflow: wf( {
			'Reported': [ { to: 'Investigating', label: 'Begin investigation', party: [ 'gc' ] } ],
			'Investigating': [ { to: 'Corrective Action', label: 'Assign corrective action', party: [ 'gc' ] } ],
			'Corrective Action': [ { to: 'Closed', label: 'Close', party: [ 'gc' ] } ],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'action-items', label: 'Assign corrective action', map: { title: 'title', description: 'corrective_action' } },
		],
		fields: [
			f( 'incident_no', 'Incident #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'incident_type', 'Type', 'select', { options: [ 'Near Miss', 'First Aid', 'Recordable Injury', 'Lost Time', 'Illness', 'Property Damage', 'Environmental' ], list: true } ),
			f( 'severity', 'Severity', 'select', { options: [ 'Low', 'Medium', 'High', 'Critical' ], list: true } ),
			f( 'incident_date', 'Date & time', 'datetime', { required: true, list: true } ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'company_involved', 'Company involved', 'text', { list: true } ),
			f( 'injured_party', 'Injured / affected party', 'text' ),
			f( 'body_part', 'Body part / nature of injury', 'text' ),
			f( 'description', 'What happened', 'textarea', { required: true } ),
			f( 'root_cause_method', 'RCA method', 'select', { options: [ '5 Whys', 'Fishbone', 'Fault Tree', 'Other' ] } ),
			f( 'root_cause', 'Root cause', 'textarea' ),
			f( 'corrective_action', 'Corrective action', 'textarea' ),
			f( 'lost_days', 'Lost days', 'number' ),
			f( 'restricted_days', 'Restricted days', 'number' ),
			f( 'osha_recordable', 'OSHA recordable', 'checkbox' ),
			f( 'reported_to_osha', 'Reported to OSHA', 'checkbox' ),
		],
	},
	{
		section: 'safety', id: 'safety-violations', name: 'Safety Violations', icon: 'bi-cone',
		workflow: wf( {
			'Open': [ { to: 'Corrected', label: 'Mark corrected', party: [ 'gc', 'subcontractor' ] } ],
			'Corrected': [
				{ to: 'Closed', label: 'Verify & close', party: [ 'gc' ] },
				{ to: 'Open', label: 'Reopen', party: [ 'gc' ] },
			],
			'Closed': [],
		} ),
		fields: [
			f( 'violation_no', 'Violation #', 'text', { list: true } ),
			f( 'company', 'Company', 'text', { required: true, list: true } ),
			f( 'violation_type', 'Type', 'select', { options: [ 'PPE', 'Fall Protection', 'Housekeeping', 'Electrical', 'Hot Work', 'Excavation', 'Other' ], list: true } ),
			f( 'severity', 'Severity', 'select', { options: [ 'Minor', 'Serious', 'Imminent Danger' ], list: true } ),
			f( 'observed_date', 'Observed', 'date', { list: true } ),
			f( 'description', 'Description', 'textarea', { required: true } ),
			f( 'corrective_action', 'Corrective action', 'textarea' ),
			f( 'fine', 'Fine / back-charge', 'currency' ),
		],
	},
	{
		section: 'safety', id: 'toolbox-talks', name: 'Toolbox Talks', icon: 'bi-chat-square-text',
		statuses: [ 'Scheduled', 'Completed' ],
		fields: [
			f( 'topic', 'Topic', 'text', { required: true, list: true } ),
			f( 'talk_date', 'Date', 'date', { required: true, list: true } ),
			f( 'presenter', 'Presenter', 'text', { list: true } ),
			f( 'company', 'Company', 'text', { list: true } ),
			f( 'attendee_count', 'Attendees', 'number', { list: true } ),
			f( 'attendees', 'Attendee names', 'textarea' ),
			f( 'notes', 'Notes', 'textarea' ),
			f( 'file_url', 'Sign-in sheet link', 'url' ),
		],
	},

	/* -------- Field productivity (expand) -------- */
	{
		section: 'field', id: 'timesheets', name: 'Timesheets', icon: 'bi-clock',
		workflow: wf( {
			'Draft': [ { to: 'Submitted', label: 'Submit', party: [ 'subcontractor', 'gc' ] } ],
			'Submitted': [
				{ to: 'Approved', label: 'Approve', party: [ 'gc' ] },
				{ to: 'Rejected', label: 'Reject', party: [ 'gc' ] },
			],
			'Approved': [],
			'Rejected': [ { to: 'Draft', label: 'Revise', party: [ 'subcontractor', 'gc' ] } ],
		} ),
		fields: [
			f( 'work_date', 'Work date', 'date', { required: true, list: true } ),
			f( 'worker_name', 'Worker', 'text', { required: true, list: true } ),
			f( 'company', 'Company', 'text', { list: true } ),
			f( 'trade', 'Trade / classification', 'text', { list: true } ),
			f( 'cost_code', 'Cost code', 'select', LOOKUP_COSTCODE ),
			f( 'regular_hours', 'Regular hours', 'number', { list: true } ),
			f( 'overtime_hours', 'OT hours', 'number', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'crews', name: 'Crews', icon: 'bi-people-fill',
		statuses: [ 'Active', 'Demobilized' ],
		fields: [
			f( 'crew_name', 'Crew name', 'text', { required: true, list: true } ),
			f( 'foreman', 'Foreman', 'text', { list: true } ),
			f( 'company', 'Company', 'text', { list: true } ),
			f( 'trade', 'Trade', 'text', { list: true } ),
			f( 'headcount', 'Headcount', 'number', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'production-quantities', name: 'Production Quantities', icon: 'bi-graph-up-arrow',
		statuses: [ 'Planned', 'In Progress', 'Complete' ],
		fields: [
			f( 'activity', 'Activity', 'text', { required: true, list: true } ),
			f( 'cost_code', 'Cost code', 'select', { ...LOOKUP_COSTCODE, list: true } ),
			f( 'unit', 'Unit', 'text' ),
			f( 'planned_qty', 'Planned qty', 'number' ),
			f( 'installed_qty', 'Installed qty', 'number', { list: true } ),
			f( 'work_date', 'Date', 'date', { list: true } ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'manpower-log', name: 'Manpower Log', icon: 'bi-person-lines-fill',
		statuses: [ 'Logged' ],
		fields: [
			f( 'log_date', 'Date', 'date', { required: true, list: true } ),
			f( 'company', 'Company', 'text', { required: true, list: true } ),
			f( 'trade', 'Trade', 'text', { list: true } ),
			f( 'workers', 'Workers on site', 'number', { list: true } ),
			f( 'hours', 'Total hours', 'number', { list: true } ),
			f( 'area', 'Work area', 'text' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'deliveries', name: 'Deliveries', icon: 'bi-box-arrow-in-down',
		statuses: [ 'Scheduled', 'Received', 'Partial', 'Rejected', 'Returned' ],
		fields: [
			f( 'delivery_date', 'Date', 'date', { required: true, list: true } ),
			f( 'supplier', 'Supplier', 'text', { required: true, list: true } ),
			f( 'material', 'Material', 'text', { list: true } ),
			f( 'po_number', 'PO #', 'text', { list: true } ),
			f( 'quantity', 'Quantity', 'text' ),
			f( 'received_by', 'Received by', 'text' ),
			f( 'item_condition', 'Condition', 'select', { options: [ 'OK', 'Damaged', 'Partial' ] } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'visitor-log', name: 'Visitor Log', icon: 'bi-person-badge-fill',
		statuses: [ 'Logged' ],
		fields: [
			f( 'visit_date', 'Date', 'date', { required: true, list: true } ),
			f( 'visitor_name', 'Visitor', 'text', { required: true, list: true } ),
			f( 'company', 'Company', 'text', { list: true } ),
			f( 'purpose', 'Purpose', 'text', { list: true } ),
			f( 'time_in', 'Time in', 'text' ),
			f( 'time_out', 'Time out', 'text' ),
			f( 'escort', 'Escort', 'text' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'field', id: 'equipment-log', name: 'Equipment Log', icon: 'bi-truck-front',
		statuses: [ 'On Site', 'Idle', 'Down', 'Off-Rented' ],
		fields: [
			f( 'equipment', 'Equipment', 'text', { required: true, list: true } ),
			f( 'equipment_id', 'Unit / ID', 'text', { list: true } ),
			f( 'supplier', 'Supplier / owner', 'text', { list: true } ),
			f( 'arrival_date', 'On-site date', 'date', { list: true } ),
			f( 'hours_used', 'Hours used', 'number' ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},

	/* -------- Cost / Financials (full model) -------- */
	{
		section: 'cost', id: 'commitments', name: 'Commitments (POs)', icon: 'bi-file-earmark-ruled',
		// Coded to a budget line; once executed, invoices are billed against it.
		workflow: wf( {
			'Draft': [ { to: 'Issued', label: 'Issue', party: [ 'gc' ] } ],
			'Issued': [ { to: 'Executed', label: 'Mark executed', party: [ 'gc' ] } ],
			'Executed': [ { to: 'Closed', label: 'Close', party: [ 'gc' ] } ],
			'Closed': [],
		} ),
		relations: [
			{ spawn: 'subcontractor-invoices', label: 'Bill against this commitment', map: { subcontractor: 'vendor', commitment_no: 'commitment_no' } },
		],
		fields: [
			f( 'commitment_no', 'Commitment #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'commitment_type', 'Type', 'select', { options: [ 'Subcontract', 'Purchase Order' ], required: true, list: true } ),
			f( 'vendor', 'Vendor / subcontractor', 'text', { required: true, list: true } ),
			f( 'csi_division', 'CSI division', 'select', LOOKUP_CSI ),
			f( 'cost_code', 'Cost code (budget line)', 'select', { ...LOOKUP_COSTCODE, list: true } ),
			f( 'value', 'Commitment value', 'currency', { list: true } ),
			f( 'executed_date', 'Executed', 'date', { list: true } ),
			f( 'retainage_pct', 'Retainage %', 'number', { step: '0.1' } ),
			f( 'scope', 'Scope', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'change-events', name: 'Change Events', icon: 'bi-diagram-2',
		workflow: wf( {
			'Open': [ { to: 'Pending', label: 'Price it', party: [ 'gc' ] } ],
			'Pending': [
				{ to: 'Converted', label: 'Convert to PCO', party: [ 'gc' ] },
				{ to: 'Void', label: 'Void', party: [ 'gc' ] },
			],
			'Converted': [],
			'Void': [],
		} ),
		relations: [
			{ spawn: 'potential-changes', label: 'Convert to PCO', map: { title: 'title', description: 'description', rom_estimate: 'rom_cost' } },
		],
		fields: [
			f( 'event_no', 'Event #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'event_type', 'Type', 'select', { options: [ 'RFI-driven', 'Owner Change', 'Design Change', 'Field Condition', 'Allowance', 'Other' ], list: true } ),
			f( 'rom_cost', 'ROM cost', 'currency', { list: true } ),
			f( 'rom_schedule_days', 'ROM schedule (days)', 'number' ),
			f( 'cost_code', 'Cost code', 'select', LOOKUP_COSTCODE ),
			f( 'description', 'Description', 'textarea', { required: true } ),
		],
	},
	{
		section: 'cost', id: 'owner-invoices', name: 'Owner Invoices (Requisitions)', icon: 'bi-receipt-cutoff',
		workflow: wf( {
			'Draft': [ { to: 'Submitted', label: 'Submit to owner', party: [ 'gc' ] } ],
			'Submitted': [
				{ to: 'Approved', label: 'Approve', party: [ 'owner', 'rep' ] },
				{ to: 'Rejected', label: 'Reject', party: [ 'owner', 'rep' ] },
			],
			'Approved': [ { to: 'Paid', label: 'Mark paid', party: [ 'owner', 'rep' ] } ],
			'Rejected': [ { to: 'Draft', label: 'Revise', party: [ 'gc' ] } ],
			'Paid': [],
		} ),
		fields: [
			f( 'invoice_no', 'Application #', 'text', { required: true, list: true } ),
			f( 'period_to', 'Period to', 'date', { required: true, list: true } ),
			f( 'contract_value', 'Contract value', 'currency' ),
			f( 'work_completed', 'Work completed to date', 'currency', { list: true } ),
			f( 'stored_materials', 'Stored materials', 'currency' ),
			f( 'retainage', 'Retainage', 'currency' ),
			f( 'amount_due', 'Amount due', 'currency', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'subcontractor-invoices', name: 'Subcontractor Invoices', icon: 'bi-receipt',
		workflow: wf( {
			'Received': [ { to: 'Under Review', label: 'Begin review', party: [ 'gc' ] } ],
			'Under Review': [
				{ to: 'Approved', label: 'Approve', party: [ 'gc' ] },
				{ to: 'Rejected', label: 'Reject', party: [ 'gc' ] },
			],
			'Approved': [ { to: 'Paid', label: 'Mark paid', party: [ 'gc' ] } ],
			'Rejected': [],
			'Paid': [],
		} ),
		fields: [
			f( 'invoice_no', 'Invoice #', 'text', { required: true, list: true } ),
			f( 'subcontractor', 'Subcontractor', 'text', { required: true, list: true } ),
			f( 'commitment_no', 'Commitment #', 'text', { list: true } ),
			f( 'period_to', 'Period to', 'date', { list: true } ),
			f( 'amount', 'Invoice amount', 'currency', { list: true } ),
			f( 'retainage', 'Retainage', 'currency' ),
			f( 'approved_amount', 'Approved amount', 'currency', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'funding-sources', name: 'Funding Sources', icon: 'bi-bank',
		statuses: [ 'Active', 'Exhausted', 'Closed' ],
		fields: [
			f( 'source_name', 'Source', 'text', { required: true, list: true } ),
			f( 'funding_type', 'Type', 'select', { options: [ 'Owner Equity', 'Construction Loan', 'Grant', 'Bond', 'Public Appropriation', 'Other' ], list: true } ),
			f( 'amount', 'Amount', 'currency', { list: true } ),
			f( 'reference', 'Reference', 'text' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'allowances', name: 'Allowances', icon: 'bi-wallet2',
		statuses: [ 'Open', 'Partially Drawn', 'Reconciled', 'Closed' ],
		fields: [
			f( 'allowance_no', 'Allowance #', 'text', { required: true, list: true } ),
			f( 'description', 'Description', 'text', { required: true, list: true } ),
			f( 'budgeted', 'Budgeted', 'currency', { list: true } ),
			f( 'used', 'Used', 'currency', { list: true } ),
			f( 'remaining', 'Remaining', 'currency', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'contingency-log', name: 'Contingency Log', icon: 'bi-shield-exclamation',
		statuses: [ 'Available', 'Pending', 'Used' ],
		fields: [
			f( 'entry_no', 'Entry #', 'text', { required: true, list: true } ),
			f( 'description', 'Description', 'text', { required: true, list: true } ),
			f( 'contingency_type', 'Type', 'select', { options: [ 'Owner', 'GC / CM', 'Design' ], list: true } ),
			f( 'amount', 'Amount', 'currency', { list: true } ),
			f( 'entry_date', 'Date', 'date', { list: true } ),
			f( 'reference', 'Reference', 'text' ),
		],
	},

	/* -------- Sustainability (new section) -------- */
	{
		section: 'sustainability', id: 'leed-credits', name: 'LEED / Green Credits', icon: 'bi-award-fill',
		statuses: [ 'Not Pursued', 'Targeted', 'In Progress', 'Documented', 'Submitted', 'Awarded', 'Denied' ],
		fields: [
			f( 'credit_id', 'Credit ID', 'text', { required: true, list: true } ),
			f( 'credit_name', 'Credit name', 'text', { required: true, list: true } ),
			f( 'category', 'Category', 'select', { options: [ 'Integrative Process', 'Location & Transportation', 'Sustainable Sites', 'Water Efficiency', 'Energy & Atmosphere', 'Materials & Resources', 'Indoor Environmental Quality', 'Innovation', 'Regional Priority' ], list: true } ),
			f( 'points_targeted', 'Points targeted', 'number', { list: true } ),
			f( 'points_achieved', 'Points achieved', 'number', { list: true } ),
			f( 'responsible', 'Responsible', 'text', { list: true } ),
			f( 'notes', 'Documentation notes', 'textarea' ),
		],
	},
	{
		section: 'sustainability', id: 'waste-diversion', name: 'Waste Diversion', icon: 'bi-recycle',
		statuses: [ 'Logged' ],
		fields: [
			f( 'log_date', 'Date', 'date', { required: true, list: true } ),
			f( 'material', 'Material', 'select', { options: [ 'Concrete', 'Metal', 'Wood', 'Cardboard', 'Drywall', 'Mixed C&D', 'Hazardous', 'Other' ], list: true } ),
			f( 'weight_tons', 'Weight (tons)', 'number', { list: true } ),
			f( 'disposal_method', 'Method', 'select', { options: [ 'Recycled', 'Reused', 'Donated', 'Landfill' ], list: true } ),
			f( 'hauler', 'Hauler', 'text', { list: true } ),
			f( 'ticket_no', 'Ticket #', 'text' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'sustainability', id: 'environmental-monitoring', name: 'Environmental Monitoring', icon: 'bi-moisture',
		statuses: [ 'Logged', 'Exceedance', 'Resolved' ],
		fields: [
			f( 'monitor_date', 'Date', 'date', { required: true, list: true } ),
			f( 'parameter', 'Parameter', 'select', { options: [ 'Stormwater (SWPPP)', 'Dust', 'Noise', 'Erosion', 'Spill', 'Air Quality' ], list: true } ),
			f( 'reading', 'Reading', 'text', { list: true } ),
			f( 'permit_limit', 'Permit limit', 'text' ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'action_taken', 'Action taken', 'textarea' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},

	/* -------- Closeout / Handover (expand) -------- */
	{
		section: 'closeout', id: 'commissioning', name: 'Commissioning (Cx)', icon: 'bi-toggles',
		workflow: wf( {
			'Not Started': [ { to: 'In Progress', label: 'Start', party: [ 'gc', 'subcontractor' ] } ],
			'In Progress': [ { to: 'Functional Test', label: 'Functional test', party: [ 'gc', 'consultant' ] } ],
			'Functional Test': [
				{ to: 'Accepted', label: 'Accept', party: [ 'consultant', 'owner', 'rep' ] },
				{ to: 'Issues', label: 'Log issues', party: [ 'gc', 'consultant' ] },
			],
			'Issues': [ { to: 'Functional Test', label: 'Re-test', party: [ 'gc' ] } ],
			'Accepted': [],
		} ),
		relations: [
			{ spawn: 'deficiencies', label: 'Log Cx issue as deficiency', map: { title: 'system', description: 'findings' } },
			{ spawn: 'action-items', label: 'Assign Cx action', map: { title: 'system', description: 'findings' } },
		],
		fields: [
			f( 'system', 'System', 'text', { required: true, list: true } ),
			f( 'equipment_tag', 'Equipment tag', 'text', { list: true } ),
			f( 'cx_phase', 'Cx phase', 'select', { options: [ 'Design Review', 'Construction', 'Functional Testing', 'Seasonal', 'Re-commissioning' ], list: true } ),
			f( 'scheduled_date', 'Scheduled', 'date', { list: true } ),
			f( 'responsible', 'Cx agent', 'text', { list: true } ),
			f( 'findings', 'Findings / issues', 'textarea' ),
			f( 'file_url', 'Cx report link', 'url' ),
		],
	},
	{
		section: 'closeout', id: 'as-builts', name: 'As-Builts / Record Drawings', icon: 'bi-pencil-square',
		// Ball-in-court: trade marks up → submits → GC/consultant accepts.
		workflow: wf( {
			'Not Started': [ { to: 'In Progress', label: 'Start markup', party: [ 'subcontractor', 'gc' ] } ],
			'In Progress': [ { to: 'Submitted', label: 'Submit', party: [ 'subcontractor', 'gc' ] } ],
			'Submitted': [
				{ to: 'Accepted', label: 'Accept', party: [ 'gc', 'consultant' ] },
				{ to: 'In Progress', label: 'Return for correction', party: [ 'gc', 'consultant' ] },
			],
			'Accepted': [],
		} ),
		fields: [
			f( 'drawing_no', 'Drawing #', 'text', { required: true, list: true } ),
			f( 'title', 'Title', 'text', { required: true, list: true } ),
			f( 'discipline', 'Discipline', 'select', { options: [ 'Architectural', 'Structural', 'Civil', 'Mechanical', 'Electrical', 'Plumbing', 'Other' ], list: true } ),
			f( 'trade', 'Responsible trade', 'text', { list: true } ),
			f( 'submitted_date', 'Submitted', 'date', { list: true } ),
			f( 'file_url', 'Record set link', 'url' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'closeout', id: 'training-records', name: 'Owner Training', icon: 'bi-easel',
		workflow: wf( {
			'Scheduled': [ { to: 'Completed', label: 'Mark completed', party: [ 'gc', 'subcontractor' ] } ],
			'Completed': [],
		} ),
		fields: [
			f( 'system', 'System', 'text', { required: true, list: true } ),
			f( 'title', 'Session title', 'text', { required: true, list: true } ),
			f( 'training_date', 'Date', 'date', { list: true } ),
			f( 'trainer', 'Trainer', 'text', { list: true } ),
			f( 'attendee_count', 'Attendees', 'number' ),
			f( 'file_url', 'Recording / materials link', 'url' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'closeout', id: 'completion-certificates', name: 'Completion Certificates', icon: 'bi-patch-check',
		// Executing a completion certificate triggers the warranty period.
		workflow: wf( {
			'Draft': [ { to: 'Issued', label: 'Issue', party: [ 'gc', 'consultant' ] } ],
			'Issued': [ { to: 'Executed', label: 'Execute', party: [ 'owner', 'rep' ] } ],
			'Executed': [],
		} ),
		relations: [
			{ spawn: 'warranties', label: 'Start warranty (period begins)', map: { item: 'description', start_date: 'issue_date' } },
		],
		fields: [
			f( 'cert_no', 'Certificate #', 'text', { required: true, list: true } ),
			f( 'cert_type', 'Type', 'select', { options: [ 'Substantial Completion', 'Final Completion', 'Partial Occupancy', 'Temporary CO', 'Certificate of Occupancy' ], required: true, list: true } ),
			f( 'description', 'Scope / area', 'text', { list: true } ),
			f( 'issue_date', 'Issue date', 'date', { list: true } ),
			f( 'value_remaining', 'Value of remaining work', 'currency' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'closeout', id: 'asset-register', name: 'Asset Register', icon: 'bi-hdd-stack',
		statuses: [ 'Active', 'Under Warranty', 'Decommissioned' ],
		relations: [
			{ spawn: 'warranties', label: 'Register warranty', map: { item: 'asset_name', company: 'manufacturer', end_date: 'warranty_end' } },
		],
		fields: [
			f( 'asset_tag', 'Asset tag', 'text', { required: true, list: true } ),
			f( 'asset_name', 'Asset name', 'text', { required: true, list: true } ),
			f( 'system', 'System', 'select', { options: [ 'HVAC', 'Electrical', 'Plumbing', 'Fire / Life Safety', 'Conveying', 'Envelope', 'Other' ], list: true } ),
			f( 'location', 'Location', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'manufacturer', 'Manufacturer', 'text', { list: true } ),
			f( 'model', 'Model', 'text' ),
			f( 'serial', 'Serial #', 'text' ),
			f( 'install_date', 'Installed', 'date' ),
			f( 'warranty_end', 'Warranty ends', 'date', { list: true } ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},

	/* ==================================================================
	   ESB-INSPIRED SCHEDULING & LOGISTICS + AIA G702/G703 FINANCIALS
	   - Linear/Takt schedule with a Line-of-Balance chart (location vs time)
	   - Just-in-time site logistics and long-lead procurement
	   - Schedule of Values (G703 rows) feeding a G702 pay-application builder
	   ================================================================== */
	{
		section: 'field', id: 'linear-schedule', name: 'Linear / Takt Schedule', icon: 'bi-graph-up',
		// Location-based (line-of-balance) scheduling — the Empire State Building model.
		statuses: [ 'Planned', 'In Progress', 'Complete', 'Delayed' ],
		fields: [
			f( 'activity', 'Activity', 'text', { required: true, list: true } ),
			f( 'location', 'Location / zone', 'select', { ...LOOKUP_LOCATION, list: true } ),
			f( 'location_index', 'Location order (floor/zone #)', 'number', { required: true, list: true, help: 'Numeric order up the building / along the line — drives the Line-of-Balance Y axis.' } ),
			f( 'crew', 'Crew / trade', 'text', { list: true } ),
			f( 'takt_zone', 'Takt wagon', 'text' ),
			f( 'planned_start', 'Planned start', 'date', { required: true, list: true } ),
			f( 'planned_finish', 'Planned finish', 'date', { required: true, list: true } ),
			f( 'duration_days', 'Duration (days)', 'number' ),
			f( 'production_rate', 'Production rate', 'text', { help: 'e.g. 1 floor / day' } ),
			f( 'predecessor', 'Predecessor', 'text' ),
			f( 'percent_complete', '% complete', 'number', { list: true } ),
		],
	},
	{
		section: 'field', id: 'site-logistics', name: 'Site Logistics', icon: 'bi-cone-striped',
		// JIT delivery windows, hoist/crane and laydown booking (ESB had no on-site storage).
		statuses: [ 'Requested', 'Booked', 'In Use', 'Completed', 'Cancelled', 'Conflict' ],
		fields: [
			f( 'logistics_date', 'Date', 'date', { required: true, list: true } ),
			f( 'resource', 'Resource', 'select', { options: [ 'Tower Crane', 'Material Hoist', 'Personnel Hoist', 'Loading Dock / Gate', 'Laydown Area', 'Road / Lane Closure', 'Concrete Pump' ], required: true, list: true } ),
			f( 'activity', 'Activity / purpose', 'text', { required: true, list: true } ),
			f( 'company', 'Company', 'text', { list: true } ),
			f( 'time_window', 'Time window', 'text', { list: true, help: 'e.g. 07:00–09:00' } ),
			f( 'location', 'Location', 'select', LOOKUP_LOCATION ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'engineering', id: 'procurement-log', name: 'Procurement Log (Long-Lead)', icon: 'bi-truck-flatbed',
		// Lead-time tracking that ties fabrication/delivery to the schedule (JIT).
		statuses: [ 'Not Ordered', 'Ordered', 'In Fabrication', 'Shipped', 'Delivered', 'Installed' ],
		fields: [
			f( 'item', 'Item / package', 'text', { required: true, list: true } ),
			f( 'csi_division', 'CSI division', 'select', { ...LOOKUP_CSI, list: true } ),
			f( 'supplier', 'Supplier / fabricator', 'text', { list: true } ),
			f( 'lead_time_weeks', 'Lead time (weeks)', 'number', { list: true } ),
			f( 'order_by', 'Order by', 'date', { list: true } ),
			f( 'need_by', 'Need on site by', 'date', { list: true } ),
			f( 'submittal_ref', 'Submittal reference', 'text' ),
			f( 'notes', 'Notes', 'textarea' ),
		],
	},
	{
		section: 'cost', id: 'schedule-of-values', name: 'Schedule of Values (G703)', icon: 'bi-list-columns',
		// AIA G703 continuation-sheet line items; totals auto-compute on the form.
		statuses: [ 'Active', 'Closed' ],
		fields: [
			f( 'item_no', 'Item # (Col A)', 'text', { required: true, list: true } ),
			f( 'description', 'Description of work (Col B)', 'text', { required: true, list: true } ),
			f( 'scheduled_value', 'Scheduled value (Col C)', 'currency', { required: true, list: true } ),
			f( 'previous_completed', 'From previous application (Col D)', 'currency' ),
			f( 'this_period', 'This period (Col E)', 'currency' ),
			f( 'materials_stored', 'Materials stored (Col F)', 'currency' ),
			f( 'total_completed', 'Total completed & stored (Col G)', 'currency', { list: true } ),
			f( 'percent_complete', '% (G/C)', 'number', { list: true } ),
			f( 'balance_to_finish', 'Balance to finish (Col H)', 'currency', { list: true } ),
			f( 'retainage', 'Retainage (Col I)', 'currency' ),
		],
	},

	/* ------------------------------------------------------------------ Settings */
	{
		section: 'settings', id: 'companies', name: 'Company Management', icon: 'bi-building',
		statuses: [ 'Active', 'Inactive' ],
		fields: [
			f( 'name', 'Company name', 'text', { required: true, list: true } ),
			f( 'type', 'Type', 'select', { options: [ 'General Contractor', 'Subcontractor', 'Owner', 'Architect', 'Engineer', 'Supplier', 'Consultant', 'Other' ], list: true } ),
			f( 'phone', 'Phone', 'text', { list: true } ),
			f( 'email', 'Email', 'email', { list: true } ),
			f( 'address', 'Address', 'text' ),
		],
	},
	{
		section: 'settings', id: 'project-help', name: 'Project Help', icon: 'bi-question-square',
		statuses: [ 'Draft', 'Published', 'Archived' ],
		fields: [
			f( 'title', 'Topic', 'text', { required: true, list: true } ),
			f( 'category', 'Category', 'select', { options: [ 'Getting Started', 'Modules', 'Workflows', 'Contacts', 'FAQ' ], list: true } ),
			f( 'body', 'Help content', 'textarea', { required: true } ),
		],
	},
];

/* Custom (JS-enhanced) modules whose folders are created with extra files separately:
   reports, project-info, user-management, bim-models (viewer), daily-reports (weather). */

// MySQL column types — mirror EM_DB::column_type() in PHP (the runtime source of truth).
const SQL_TYPES = {
	text: 'varchar(191)', select: 'varchar(191)', email: 'varchar(191)', url: 'varchar(191)',
	textarea: 'text', richtext: 'text', json: 'longtext', signature: 'mediumtext',
	number: 'decimal(20,4)', currency: 'decimal(20,4)',
	date: 'date', datetime: 'datetime', checkbox: 'tinyint(1)',
};
const NON_INDEXABLE = [ 'text', 'longtext', 'mediumtext' ];

let sql = `-- ============================================================
-- eManager — MySQL reference schema
-- ------------------------------------------------------------
-- FOR REFERENCE ONLY. eManager creates and updates these tables
-- automatically (via dbDelta) on activation and whenever a module
-- is installed; see includes/class-em-db.php. Table names below
-- omit the WordPress table prefix (e.g. wp_).
-- Generated by tools/generate-modules.js.
-- ============================================================

CREATE TABLE em_companies (
  id bigint(20) unsigned NOT NULL auto_increment,
  name varchar(191) DEFAULT NULL,
  type varchar(191) DEFAULT NULL,
  phone varchar(191) DEFAULT NULL,
  email varchar(191) DEFAULT NULL,
  address text DEFAULT NULL,
  status varchar(191) DEFAULT NULL,
  created_at datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY name (name)
);

CREATE TABLE em_comments (
  id bigint(20) unsigned NOT NULL auto_increment,
  module_id varchar(191) DEFAULT NULL,
  record_id bigint(20) unsigned DEFAULT NULL,
  body text DEFAULT NULL,
  author_id varchar(20) DEFAULT NULL,
  author_name varchar(191) DEFAULT NULL,
  created_at datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY record (module_id,record_id)
);

CREATE TABLE em_activity (
  id bigint(20) unsigned NOT NULL auto_increment,
  module_id varchar(191) DEFAULT NULL,
  record_id bigint(20) unsigned DEFAULT NULL,
  actor_id varchar(20) DEFAULT NULL,
  actor_name varchar(191) DEFAULT NULL,
  action varchar(40) DEFAULT NULL,
  from_status varchar(191) DEFAULT NULL,
  to_status varchar(191) DEFAULT NULL,
  note text DEFAULT NULL,
  created_at datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY record (module_id,record_id)
);
`;

const COMMON_COLS = `  id bigint(20) unsigned NOT NULL auto_increment,
  project_id varchar(191) DEFAULT NULL,
  company_id varchar(191) DEFAULT NULL,
  status varchar(191) DEFAULT NULL,
  direction varchar(32) DEFAULT NULL,
  linked_module varchar(191) DEFAULT NULL,
  linked_id bigint(20) unsigned DEFAULT NULL,
  created_by varchar(20) DEFAULT NULL,
  created_by_name varchar(191) DEFAULT NULL,
  created_at datetime DEFAULT NULL,
  updated_at datetime DEFAULT NULL`;

const COMMON_KEYS = `  PRIMARY KEY  (id),
  KEY status (status),
  KEY created_at (created_at),
  KEY created_by (created_by),
  KEY project_status (project_id,status),
  KEY linked (linked_module,linked_id)`;

let tableCount = 3; // companies + comments + activity

for ( const m of MODULES ) {
	const table = 'em_' + m.id.replace( /-/g, '_' );
	tableCount++;

	// 1. Write module.json. When a workflow is declared, its state names ARE
	//    the status list, so list filters and badges stay in sync.
	const statuses = m.workflow ? Object.keys( m.workflow.states ) : m.statuses;
	const dir = path.join( ROOT, 'modules', m.section, m.id );
	fs.mkdirSync( dir, { recursive: true } );
	const def = {
		id: m.id,
		name: m.name,
		section: m.section,
		icon: m.icon,
		table,
		version: '1.0.0',
		statuses,
		fields: m.fields,
	};
	if ( m.workflow ) {
		def.workflow = m.workflow;
	}
	if ( m.relations ) {
		def.relations = m.relations;
	}
	fs.writeFileSync( path.join( dir, 'module.json' ), JSON.stringify( def, null, '\t' ) + '\n' );

	// 2. Append a reference CREATE TABLE with per-field columns + indexes.
	const fieldLines = [];
	const fieldKeys = [];
	for ( const fld of m.fields ) {
		const type = SQL_TYPES[ fld.type ] || 'varchar(191)';
		fieldLines.push( `  \`${ fld.name }\` ${ type } DEFAULT NULL` );
		if ( fld.list && ! NON_INDEXABLE.includes( type ) ) {
			fieldKeys.push( `  KEY \`idx_${ fld.name }\` (\`${ fld.name }\`)` );
		}
	}
	const keys = fieldKeys.length ? COMMON_KEYS + ',\n' + fieldKeys.join( ',\n' ) : COMMON_KEYS;
	sql += `\n-- ${ m.name } (${ m.section })\nCREATE TABLE ${ table } (\n${ COMMON_COLS },\n${ fieldLines.join( ',\n' ) },\n${ keys }\n);\n`;
}

fs.mkdirSync( path.join( ROOT, 'schema' ), { recursive: true } );
fs.writeFileSync( path.join( ROOT, 'schema', 'schema-reference.sql' ), sql );

console.log( `Generated ${ MODULES.length } module.json files and schema/schema-reference.sql (${ tableCount } tables).` );
