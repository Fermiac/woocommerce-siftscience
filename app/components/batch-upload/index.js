import React from 'react';

const component = () => {
	return (
		<table className="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label>Batch Upload</label>
					</th>
					<td class="forminp forminp-text">
						<button type="button" className="button-primary" style={ { backgroundColor: 'green' } }>
							Upload
						</button>
						<p class="description">
							Send all your orders to SiftScience
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	);
};

export default component;
