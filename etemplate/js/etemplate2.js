/**
 * eGroupWare eTemplate2 - JS file which contains the complete et2 module
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Andreas Stöckel
 * @copyright Stylite 2011
 * @version $Id$
 */

/*egw:uses
	// Include all widget classes here
	et2_template;
	et2_grid;
	et2_box;
	et2_hbox;
	et2_button;
	et2_description;
	et2_textbox;
	et2_number;
	et2_selectbox;
	et2_checkbox;
	et2_radiobox;
	et2_date;
	et2_styles;
	et2_html;
	et2_tabs;
	et2_hrule;

	// Requirements for the etemplate2 object
	et2_xml;
	et2_arrayMgr;
*/

/**
 * The etemplate2 class manages a certain etemplate2 instance.
 *
 * @param _container is the DOM-Node into which the DOM-Nodes of this instance
 * 	should be inserted
 * @param _submitURL is the URL to which the form data should be submitted.
 */
function etemplate2(_container, _menuaction)
{
	if (typeof _menuaction == "undefined")
	{
		_menuaction = "etemplate_new::ajax_process_content";
	}

	// Copy the given parameters
	this.DOMContainer = _container;
	this.menuaction = _menuaction;

	// Preset the object variable
	this.widgetContainer = null;

	// Associative array with the event listeners
	this.listeners = {};
}

/**
 * Clears the current instance.
 */
etemplate2.prototype.clear = function()
{
	if (this.widgetContainer != null)
	{
//		$j(':input',this.DOMContainer).validator().data("validator").destroy();
		this.widgetContainer.destroy();
		this.widgetContainer = null;
	}
}

/**
 * Creates an associative array containing the data array managers for each part
 * of the associative data array. A part is something like "content", "readonlys"
 * or "sel_options".
 */
etemplate2.prototype._createArrayManagers = function(_data)
{
	if (typeof _data == "undefined")
	{
		_data = {};
	}

	// Create all neccessary _data entries
	var neededEntries = ["content", "sel_options", "readonlys", "modifications", "validation_errors"];
	for (var i = 0; i < neededEntries.length; i++)
	{
		if (typeof _data[neededEntries[i]] == "undefined")
		{
			et2_debug("log", "Created not passed entry '" + neededEntries[i] + "' in data array.");
			_data[neededEntries[i]] = {};
		}
	}

	var result = {};

	// Create an array manager object for each part of the _data array.
	for (var key in _data)
	{
		switch (key) {
			case "etemplate_exec_id":	// already processed
			case "app_header":
				break;
			case "readonlys":
				result[key] = new et2_readonlysArrayMgr(_data[key]);
				break;
			default:
				result[key] = new et2_arrayMgr(_data[key]);
		}
	}

	return result;
}

/**
 * Loads the template from the given URL and sets the data object
 */
etemplate2.prototype.load = function(_url, _data)
{
	// Asynchronously load the XET file (code below is executed ahead of the
	// code in the callback function)
	et2_loadXMLFromURL(_url, function(_xmldoc) {
		// Read the XML structure
		this.widgetContainer.loadFromXML(_xmldoc);
		// Inform the widget tree that it has been successfully loaded.
		this.widgetContainer.loadingFinished();
	}, this);

	// Clear any existing instance
	this.clear();

	// Create the basic widget container and attach it to the DOM
	this.widgetContainer = new et2_container(null);
	this.widgetContainer.setInstanceManager(this);
	this.widgetContainer.setParentDOMNode(this.DOMContainer);

	// store the id to submit it back to server
	if(_data) {
		this.etemplate_exec_id = _data.etemplate_exec_id;
	}
	
	// set app_header
	if (window.opener) {	// popup
		document.title = _data.app_header;
	} else {
		// todo for idots or jdots framework
	}

	// Split the given data into array manager objects and pass those to the
	// widget container
	this.widgetContainer.setArrayMgrs(this._createArrayManagers(_data));
}

etemplate2.prototype.submit = function()
{
	// Validator
	/*var valid = true;
	var inputs = $j(':input',this.DOMContainer).each(function() {
		if(typeof $j(this).data("validator") == "undefined") return true;
		valid = valid && $j(this).data("validator").checkValidity();
		return true;
	});
	if(!valid) return false;*/

	// Get the form values
	var values = this.widgetContainer.getValues();

	// Trigger the submit event
	if (this.fireEvent("submit", [values]))
	{
		// Create the request object
		if (typeof egw_json_request != "undefined")
		{
			var request = new egw_json_request(this.menuaction, [this.etemplate_exec_id,values], this);
			request.sendRequest(true);
		}
		else
		{
			et2_debug("info", "Form got submitted with values: ", values);
		}
	}
}

/**
 * Adds an callback function to the given event slot
 * 
 * @param _event is the name of the event
 * @param _callback is the function which should be called once the event gets
 * 	fired.
 * @param _context is the context in which the function should be executed.
 */
etemplate2.prototype.addListener = function(_event, _callback, _context)
{
	// Add the event slot if it does not exist yet
	if (typeof this.listeners[_event] == "undefined")
	{
		this.listeners[_event] = [];
	}

	this.listeners[_event].push({
		"callback": _callback,
		"context": _context
	});
}

/**
 * Removes the given callback function from the given event slot.
 */
etemplate2.prototype.removeListener = function(_event, _callback)
{
	if (typeof this.listeners[_event] != "undefined")
	{
		var events = this.listeners[_event];

		for (var i = events.length - 1; i >= 0; i--)
		{
			if (events[i].callback == _callback)
			{
				events.splice(i, 1);
			}
		}
	}
}

/**
 * Fires the given event. The return values are conected via AND
 */
etemplate2.prototype.fireEvent = function(_event, _args)
{
	if (typeof _args == "undefined")
	{
		_args = [];
	}

	var result = true;

	if (typeof this.listeners[_event] != "undefined")
	{
		var events = this.listeners[_event];

		for (var i = 0; i < events.length; i++)
		{
			result = result && events[i].callback.apply(events[i].context, _args);
		}
	}

	return result;
}

/**
 * Function which handles the EGW JSON et2_load response
 */
function etemplate2_handle_response(_type, _response)
{
	if (_type == "et2_load")
	{
		// Check the parameters
		var data = _response.data;
		if (typeof data.url == "string" && data.data instanceof Object)
		{
			this.load(data.url, data.data);
			return true;
		}

		throw("Error while parsing et2_load response");
	} else if (_type == "et2_validation_error") {
		// Display validation errors
//		$j(':input',this.DOMContainer).data("validator").invalidate(_response.data);
	}

	return false;
}

// Register the egw_json result object
if (typeof egw_json_register_plugin != "undefined")
{
	// Calls etemplate2_handle_response in the context of the object which
	// requested the response from the server
	egw_json_register_plugin(etemplate2_handle_response, null);
}
else
{
	et2_debug("info", "EGW JSON Plugin could not be registered, running ET2 standalone.");
}

