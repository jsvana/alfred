#include <glib.h>
#include <json.h>
#include <string.h>
#include "xbmc.h"
#include "utils.h"
#include "json.h"
#include "error.h"

#define XBMC_USER "xbmc"
#define XBMC_PASS "1123581321!"
#define XBMC_HOST "garkin.dyndns.org"
#define XBMC_PORT "8080"

json_object *alfred_xbmc_json_new(const char *method, int player, json_object *params) {
	json_object *obj = json_object_new_object();
	
	json_object *id = json_object_new_int(1);
	json_object *rpc = json_object_new_string("2.0");
	json_object *method_obj = json_object_new_string(method);
	if (!params) {
		params = json_object_new_object();
	}

	if (player) {
		json_object *pid = json_object_new_int(0);
		json_object_object_add(params, "playerid", pid);
	}

	json_object_object_add(obj, "jsonrpc", rpc);
	json_object_object_add(obj, "method", method_obj);
	json_object_object_add(obj, "params", params);
	json_object_object_add(obj, "id", id);

	return obj;
}

void alfred_xbmc_json_free(json_object *obj) {
	json_object_put(obj);
}

char *alfred_xbmc_request_new(const char *method, int player, json_object *params) {
	json_object *obj = alfred_xbmc_json_new(method, player, params);

	char *data = alfred_utils_curl_post_login("http://" XBMC_HOST ":" XBMC_PORT "/jsonrpc", json_object_to_json_string(obj), XBMC_USER, XBMC_PASS);

	alfred_xbmc_json_free(obj);

	json_object *msg_params = json_object_new_object();
	json_object *msg = json_object_new_string("Command sent.");
	json_object_object_add(msg_params, "message", msg);
	alfred_json_create_display(0, "Method success.", msg_params);

	return data;
}

void alfred_xbmc_request_free(char *data) {
	g_free(data);
}

void alfred_xbmc_request(const char *method, int player, json_object *params) {
	alfred_xbmc_request_free(alfred_xbmc_request_new(method, player, params));
}

void alfred_module_xbmc(const char *command, json_object *params) {
	if (alfred_authed()) {
		if (strcmp(command, "Pause") == 0) {
			alfred_xbmc_request("Player.PlayPause", 1, NULL);
		} else if (strcmp(command, "Next") == 0) {
			alfred_xbmc_request("Player.GoNext", 1, NULL);
		} else if (strcmp(command, "Previous") == 0) {
			alfred_xbmc_request("Player.GoPrevious", 1, NULL);
		} else if (strcmp(command, "Shuffle") == 0) {
			alfred_xbmc_request("Player.Shuffle", 1, NULL);
		} else if (strcmp(command, "Mute") == 0) {
			json_object *params = json_object_new_object();
			json_object *mute = json_object_new_string("true");
			json_object_object_add(params, "mute", mute);
			alfred_xbmc_request("Application.SetMute", 0, params);
		} else if (strcmp(command, "Unmute") == 0) {
			json_object *params = json_object_new_object();
			json_object *mute = json_object_new_string("false");
			json_object_object_add(params, "mute", mute);
			alfred_xbmc_request("Application.SetMute", 0, params);
		} else if (strcmp(command, "Volume") == 0) {
			char *volume = alfred_json_get_string(params, "volume");

			if (volume) {
				json_object *params = json_object_new_object();
				json_object *vol = json_object_new_string("false");
				json_object_object_add(params, "volume", vol);
				alfred_xbmc_request("Application.SetVolume", 0, params);
			} else {
				alfred_error_static(ALFRED_ERROR_INCORRECT_PARAMS);
			}
		} else if (strcmp(command, "Up") == 0) {
			alfred_xbmc_request("Input.Up", 0, params);
		} else if (strcmp(command, "Down") == 0) {
			alfred_xbmc_request("Input.Down", 0, params);
		} else if (strcmp(command, "Left") == 0) {
			alfred_xbmc_request("Input.Left", 0, params);
		} else if (strcmp(command, "Right") == 0) {
			alfred_xbmc_request("Input.Right", 0, params);
		} else if (strcmp(command, "Select") == 0) {
			alfred_xbmc_request("Input.Select", 0, params);
		} else {
			alfred_error_static(ALFRED_ERROR_UNKNOWN_COMMAND);
		}
	} else {
		alfred_error_static(ALFRED_ERROR_NOT_AUTHENTICATED);
	}
}
