set(EXECUTABLE_OUTPUT_PATH "${CMAKE_BINARY_DIR}")
add_executable(
	alfred.rpc
	main.c
	json.c
	error.c
	sql.c
	utils.c

	# Modules
	alfred.c
	location.c
	net.c
	password.c
	xbmc.c
)

target_link_libraries(
	alfred.rpc
	${JANSSON_LIBRARIES}
	${GLIB_LIBRARIES}
	${MYSQL_LIBRARIES}
)
