cmake_minimum_required(VERSION 2.6)
set(CMAKE_MODULE_PATH "${CMAKE_SOURCE_DIR}/cmake")
include(FindPkgConfig)

project(alfred)

find_package(Glib REQUIRED gmodule)
find_package(Jansson REQUIRED)
find_package(MySQL REQUIRED)

# TODO: Change from json-c to jansson

include_directories(
	${GLIB_INCLUDE_DIR}
	${MYSQL_INCLUDE_DIR}
	${JANSSON_INCLUDE_DIRS}
)

add_subdirectory(src)
add_subdirectory(modules)
