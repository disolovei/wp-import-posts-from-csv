(function($) {
	const loadDataButton	= document.getElementById('load-data');
	const saveDataButton	= document.getElementById('save-data');
	const csvInput			= document.getElementById('gstp-csv-file');
	const wrapper 			= document.getElementById('posts-list-tbody');

	const DataPresentation = {
		_target: null,
		init: function(target) {
			if ( target && target instanceof HTMLElement ) {
				this._target = target;
			}
		},
		buildCell: function( cellText = '' ) {
			const td = document.createElement('td');
			td.innerText = cellText;
			return td;
		},
		buildRow: function( rowData ) {
			if ( ! Array.isArray( rowData ) || rowData.length < 2 ) {
				return;
			}

			if ( ! this._target ) {
				return;
			}

			const tr = document.createElement('tr');
			tr.classList.add('no');

			const [postTitle, ...other] = rowData;
			const relatedPostID = !!rowData[2] ? rowData[2] : '-';
			const postType = !!rowData[3] ? rowData[3] : 'post';

			tr.appendChild(this.buildCell(postTitle));
			tr.appendChild(this.buildCell(relatedPostID));
			tr.appendChild(this.buildCell(postType));
			tr.appendChild(this.buildCell('-'));

			this._target.appendChild(tr);
		},
		updateCounter: function(count) {
			document.getElementById('total-items').innerText = count.toString();
		},
	};

	if ( wrapper ) {
		DataPresentation.init(wrapper);
	}

	const GSTP = {
		data: [],
		init: function() {

			if ( document.getElementById('file-reader') ) {
				this.FileReader._init();
			} else if ( document.getElementById('api-reader') ) {
				this.APIReader._init();
			}

			saveDataButton.addEventListener('click', GSTP.save);
		},
		APIReader: {
			_config: {},
			_init: function() {
				if ( 'undefined' === typeof gapi ) {
					Logger.error('Missed gapi script!');
					console.error('Missed gapi script!');
					return;
				}

				if ( 'undefined' === typeof GSTPAPI ) {
					Logger.error('Check API credentials!');
					return;
				}

				loadDataButton.addEventListener('click', this.listMajors);

				document.getElementById('authorize_button').addEventListener('click', this._handleAuthClick);
				document.getElementById('signout_button').addEventListener('click', this._handleSignoutClick);

				gapi.load('client:auth2', this._load);
			},
			_load: function() {
				if ( !GSTPAPI.API_KEY || !GSTPAPI.CLIENT_ID || !GSTPAPI.SHEET_ID || !GSTPAPI.SHEET_NAME ) {
					Logger.error('Check API credentials!');
					return;
				}

				gapi.client.init({
					apiKey: GSTPAPI.API_KEY,
					clientId: GSTPAPI.CLIENT_ID,
					discoveryDocs: ["https://sheets.googleapis.com/$discovery/rest?version=v4"],
					scope: "https://www.googleapis.com/auth/spreadsheets.readonly",
				}).then(function () {
					gapi.auth2.getAuthInstance().isSignedIn.listen(GSTP.APIReader._updateSigninStatus);

					GSTP.APIReader._updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());

					Logger.success('Ready!');
				}, function(error) {
					Logger.error(JSON.stringify(error, null, 2));
				});
			},
			_handleAuthClick: function (event) {
				gapi.auth2.getAuthInstance().signIn();
			},
			_handleSignoutClick: function (event) {
				if ( ! confirm( 'Are you sure?' ) ) {
					return true;
				}

				gapi.auth2.getAuthInstance().signOut();
			},
			_updateSigninStatus: function (isSignedIn) {
				if ( isSignedIn ) {
					[].forEach.call(document.getElementsByClassName('auth-success'), function (el) {
						el.classList.remove('hide');;
					});
					[].forEach.call(document.getElementsByClassName('auth-failed'), function (el) {
						el.classList.add('hide');
					});
				} else {
					[].forEach.call(document.getElementsByClassName('auth-success'), function (el) {
						el.classList.add('hide');
					});
					[].forEach.call(document.getElementsByClassName('auth-failed'), function (el) {
						el.classList.remove('hide');
					});
				}
			},
			listMajors: function () {
				gapi.client.sheets.spreadsheets.values.get({
					spreadsheetId: GSTPAPI.SHEET_ID,
					range: `${GSTPAPI.SHEET_NAME}!A2:D`,
					majorDimension: "ROWS",
				}).then(function(response) {
					const range = response.result;

					Logger.success('Data loaded!')

					if (range.values.length > 0) {
						GSTP.data = [...range.values];

						for (let i = 0; i < GSTP.data.length; i++) {
							DataPresentation.buildRow(GSTP.data[i]);
						}

						DataPresentation.updateCounter(GSTP.data.length.toString());

						loadDataButton.classList.add('hide');
						saveDataButton.classList.remove('hide');
					} else {
						Logger.error('No data found.');
					}
				}, function(response) {
					Logger.error(`Error: ${response.result.error.message}`);
				});
			}
		},
		FileReader: {
			_init: function() {
				loadDataButton.addEventListener('click', this.read);
			},
			read: function() {
				if ( ! confirm( 'Are you sure?' ) ) {
					return true;
				}

				Logger.info('Read a CSV file!')

				if ( ! csvInput.files[0] ) {
					return true;
				}

				const reader = new FileReader();
				reader.onload = function(event) {
					const range = $.csv.toArrays(event.target.result).slice(1);

					Logger.success('The file was read successfully!')

					if (range.length > 0) {

						GSTP.data = [...range];

						for ( let i = 0; i < GSTP.data.length; i++ ) {
							DataPresentation.buildRow(GSTP.data[i]);
						}

						DataPresentation.updateCounter( GSTP.data.length );
						$('.gstp-reader .hide').removeClass('hide');
						document.getElementById('read-form').remove();
					} else {
						Logger.error('No data found.');
					}
				};
				reader.onerror = function(error) {
					console.error(error);
				};

				reader.readAsText(csvInput.files[0]);
			},
		},
		save: function() {
			if ( ! confirm( 'Are you sure?' ) ) {
				return true;
			}

			$('.gstp-reader button, .gstp-reader select').attr('disabled', 'disabled');

			this._save();
		},
		_save: function() {
			if ( ! Array.isArray(GSTP.data) ) {
				Logger.error('Wrong data type!');
				return true;
			}

			if ( ! GSTP.data.length ) {
				alert('Success');
				Logger.success('Job end!');
				return true;
			}

			try {
				const row = GSTP.data.shift();
				const [postTitle, postContent] = row;
				const relatedPostID = !!row[2] ? row[2] : 0;
				const postType = !!row[3] ? row[3] : 'post';

				const blogSelect = document.getElementById('merge-via-blog');
				let blogId = -1;

				if ( blogSelect ) {
					blogId = blogSelect.value;
				}

				$.post(ajaxurl, {action: 'gstp_save_post', postTitle, postContent, relatedPostID, postType, blogId }, function(response) {
					setTimeout(GSTP._save, 1000);

					if ( response.data ) {
						const firstNoTr = wrapper.querySelector('.no');

						if ( firstNoTr ) {
							firstNoTr.classList.remove('no');
							const tds = firstNoTr.querySelectorAll('td');

							if ( ! tds ) {
								return true;
							}

							tds[tds.length - 1].innerHTML = response.data.toString();
						}
					}
				}, 'json');
			} catch (e) {
				console.error(e.message);
			}
		},
	};

	const Logger = {
		_target: null,
		init: function(target) {
			if ( target && target instanceof HTMLElement ) {
				this._target = target;
			}
		},
		_log: function(type = 'info', message = '') {
			if ( ! message ) {
				return;
			}

			if ( ! this._target || ! this._target instanceof HTMLElement ) {
				if ( 'error' === type ) {
					console.error(message);
				} else {
					console.log(message);
				}
				return;
			}

			const messageLine = document.createElement('p');
			messageLine.classList.add('log');
			messageLine.classList.add(`log--${type.toString()}`);
			messageLine.innerHTML = message.toString();
			this._target.appendChild(messageLine);
		},
		info: function(message) {
			this._log('info', message);
		},
		success: function(message) {
			this._log('success', message);
		},
		error: function(message) {
			this._log('error', message);
		},
	};

	const logs = document.getElementById('logs');

	if ( logs ) {
		Logger.init(logs);
	}

	GSTP.init();
})(jQuery);
