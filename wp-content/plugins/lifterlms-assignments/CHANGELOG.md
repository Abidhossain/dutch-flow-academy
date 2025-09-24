LifterLMS Assignments Changelog
===============================

v2.3.3 - 2025-07-28
-------------------

##### Developer Notes

+ Adding filter to modify the limit of the submission summary merge code for essay type assignments.


v2.3.2 - 2025-04-18
-------------------

##### New Features

+ Adding RTL support to the essay assignment editor.


v2.3.1 - 2025-04-11
-------------------

##### Bug Fixes

+ Improved file name display when submitting an assignment that is protected.

##### Updated Templates

+ templates/assignment/content-upload.php


v2.3.0 - 2025-04-10
-------------------

##### New Features

+ Adds protection for uploaded assignments using the new LifterLMS media protection functionality.

##### Bug Fixes

+ Avoid fatal error if student cannot be retrieved.


v2.2.3 - 2024-12-06
-------------------

##### Bug Fixes

+ Fixes translations working with WP 6.7.


v2.2.2 - 2024-04-23
-------------------

##### Bug Fixes

+ Avoids fatal error if core is deactivated with this add-on still activated.


v2.2.1 - 2024-04-18
-------------------

##### Bug Fixes

+ Updating version number in main class file.


v2.2.0 - 2024-04-18
-------------------

##### New Features

+ Adds settings in the Permalinks page to edit the custom post type slugs. Slugs are saved in the site language on install on update.

##### Updates and Enhancements

+ Requires LifterLMS core version to be up to date in order to use this add-on with the slug customization feature.

##### Bug Fixes

+ Prevents students from uploading multiple files for an assignment or removing upload file after submission.


v2.1.0 - 2023-08-02
-------------------

##### Updates and Enhancements

+ Improve security by escaping localization string in the various templates.
+ Rounded off 'average' column values in the assignment table in reporting.
+ Raised the minimum required version of LifterLMS from 6.0.0-alpha to 7.2.0.

##### Bug Fixes

+ Early return when loading the assignment results template but there's no submisssion.
+ Updating Assignments Reporting tab to match core LifterLMS UI improvements.

##### Updated Templates

+ templates/assignment/content-upload.php
+ templates/assignment/footer.php
+ templates/assignment/results.php


v2.0.1 - 2022-10-17
-------------------

##### Bug Fixes

+ Fixed an issue with emojis in essays by not running autosave on completed essays.
+ Fixed issue causing escape characters to be added when saving essay assignment submissions.


v2.0.0 - 2022-08-30
-------------------

##### Updates and Enhancements

+ Replaced use of the deprecated `FILTER_SANITIZE_STRING` constant.

##### Bug Fixes

+ Fixed issue encountered on admin assignment submission review resulting in visual "flickering" of the Remarks textarea and / or a visual obscuring of the Grade percentage input.
+ Fixed an issue where deleting an assignment submission would not change the student's lesson status to "incomplete".
+ Automatically sync lesson author to child assignments when a lesson's `post_author` value changes.
+ Fixed a caching issue with the Firefox browser when canceling a review.

##### Developer Notes

+ **[BREAKING]** This plugin no longer includes source map `.map` files for Javascript and CSS files.
+ **[BREAKING]** This plugin no longer includes unminified Javascript or CSS files.


v1.3.0 - 2022-03-08
-------------------

**The minimum required LifterLMS core version requirement has been raised from 5.3.0 to 6.0.0. Please upgrade LifterLMS to 6.0.0 or later in order to continue using this add-on!**

##### Developer notes

+ Renamed `LLMS_Query_Assignments_Submission::preprare_query()` to `LLMS_Query_Assignments_Submission::prepare_query()`.
+ Replaced the use of the protected `LLMS_Query_Assignments_Submission::$max_pages` property with `LLMS_Query_Assignments_Submission::get( 'max_pages' )`.


v1.2.1 - 2022-02-17
-------------------

##### Bug Fixes

+ Fixed an issue where the assignments builder may not have initialized by the time the core builder initialized.


v1.2.0 - 2022-01-25
-------------------

##### Updates and Enhancements

+ Raised the minimum supported LifterLMS Core version to version 5.3.0.
+ Raised the minimum supported WordPress core version to 5.5.
+ Raised the minimum supported PHP version to 7.4.
+ Replaced deprecated `LLMS_Interface_Post_Audio` and `LLMS_Interface_Post_Video` with `LLMS_Trait_Audio_Video_Embed`.


v1.1.13 - 2021-05-17
--------------------

**The minimum LifterLMS core version requirement has been raised to version 4.21.2. Please upgrade LifterLMS to 4.21.2 or later in order to continue using this add-on!**

##### Updates

+ Uses `llms()` in favor of deprecated `LLMS()`.

#### Bug fixes

+ Uses the LifterLMS core `view_grades` capability to determine if users may view another user's grades on the website's frontend.


v1.1.12 - 2021-02-22
--------------------

+ Bug: Fixed an undefined variable error encountered when submitting an essay.
+ Bug: Updated the language of the error message returned when an error is encountered submitting an essay.
+ Conflict: Fixed a conflict with Yoast SEO causing causing a redirect to occur while cloning a course.


v1.1.11 - 2020-10-22
--------------------

+ Update: Remove usage of core `LLMS_Generator` class method scheduled to be deprecated in a future release.
+ Bugfix: Do not try to print the submission title if no submission was found for the current assignment.
+ Bugfix: Print a notice if no submission was found for the current assignment, instead of loading the assignment's submission template.


v1.1.10 - 2020-09-01
--------------------

+ Fixed a bug encountered when reviewing student course reports as a group leader using the Groups add-on.


v1.1.9 - 2020-05-06
-------------------

+ Fixed a compatibility issue encountered when using Assignments alongside LifterLMS Groups.


v1.1.8 - 2020-04-28
-------------------

+ Adds support for forthcoming changes to the "Lesson Progression" block provided by the LifterLMS core.


v1.1.7 - 2020-04-20
-------------------

+ Fixed issue which could be encountered with plugins or themes that register a custom post type named "notifications".
+ Fix issue encountered when attempting to send a notification for an assignment that's been erased from the database.


v1.1.6 - 2020-03-12
-------------------

+ When a lesson with an assignment is deleted the assignment is now automatically deleted.
+ When an assignment is deleted all submissions for the assignment are automatically deleted.
+ When an upload assignment submission is deleted the upload file (and associated attachment post) is automatically deleted.
+ Bugfix: When an assignment is deleted from the course builder is now deleted (instead of trashed) and the lesson's metadata is updated to remove the association to the deleted assignment.


v1.1.5 - 2020-03-05
-------------------

+ Bugfix: Call `exit()` after redirecting on submission deletion.
+ Bugfix: Made private method `LLMS_Assignments_Install::_106beta6_add_points()` static.
+ Bugfix: Use `gmdate()` in favor of `date()` when outputting task completion dates.


v1.1.4 - 2019-11-05
-------------------

+ Fixes an issue preventing instructors from viewing assignment reporting (requires LifterLMS Core 3.36.5 to fully resolve this issue).


v1.1.3 - 2019-06-28
-------------------

+ Fix a date transposition error visible on assignment notification emails.


v1.1.2 - 2019-05-06
-------------------

+ **Raises the minimum required LifterLMS Core version to 3.31.0. Please upgrade the LifterLMS Core in conjunction with this add-on!**
+ Improves reporting data by using the LifterLMS core data methods.


v1.1.1 - 2019-04-09
-------------------

+ Add handlers to handle LifterLMS duplicate assignments during when importing and cloning courses.


v1.1.0 - 2019-01-30
-------------------

##### Updates

+ Added formatting options (bold, italics, and underline) to task list items.
+ Added anchor/link options to task list items.
+ Added "deep" linking to assignments on the course builder from course builder metaboxes displayed when editing a lesson in the WordPress editor.

##### Bug Fixes

+ Fixed an issue causing assignment reporting screens to encounter a fatal error when user's associated with assignment submissions had been deleted from the database.

##### Templates Changed

+ templates/assignment/content-tasklist.php


v1.0.1 - 2018-12-19
-------------------

+ Fixed an issue preventing instructors from marking individual tasks complete or incomplete when grading a tasklist assignment.


v1.0.0 - 2018-11-08
-------------------

+ Initial public release


v1.0.0-beta.6 - 2018-10-23
--------------------------

##### Minimum Required LifterLMS Version Has Changed

+ **The minimum required LifterLMS core version in now 3.24.0 Please upgrade LifterLMS to the latest version to continue using LifterLMS Assignments**

##### Grading

+ Assignment grades will now contribute to the grade of a lesson
+ If no quiz is present, the lesson grade will equal the grade of the lesson
+ If both a quiz and assignment are present on a lesson, the lesson grade will be determined by a combination of the quiz and assignment grades
+ The default weight of quizzes and assignments are 1:1, by defining custom weights for the quiz and assignment it is now possible to weight a quiz or assignment more heavily (or make one not count at all by defining a weight of 0)
+ The grade of the lesson is calculated by the following formula: `( quiz_grade x quiz_weight ) + ( assignment_grade x assignment_weigth ) / ( quiz_weight + assignment_weigth )`. If the total weight is 0, the lesson grade will be `null` which results in the lesson not counting towards the overall grade of the course.
+ Existing course and lesson grades will automatically be updated after a database migration. Existing quizzes and assignments will all be set with a default weight of 1 point.

##### Notifications

+ Customizable email notifications are now available
+ Assignment Graded: an email sent to the student when their assignment has been manually graded by an instructor
+ Assignment Submitted: an email sent to the instructor when an assignment has been submitted by a student

###### Updates and Enhancements

+ Added assignment information on the "My Grades" area of the student dashboard
+ Added assignment information to the student course reporting table
+ Added submission review url getter method to submission model
+ Added Links between courses and assignments on reporting screens
+ Assignment submission data will now be included in personal data exports
+ Assignment submission data will now be deleted from the database during personal data erasure requests (only if "Remove Student LMS Data" is enabled in account settings)

##### Bug fixes

+ When a lesson is a deleted any associated lessons will also be deleted
+ When an assignment is graded the action `llms_assignment_submitted` is no longer called and `llms_assignment_graded` is now called instead. `llms_assignment_submitted` is reserved for student submission only.
+ Fixed issue with mimetype validation for upload assignments

##### Templates Changed

+ templates/assignment/content-upload.php
+ templates/assignment/results.php
+ templates/assignment/lesson/take-assignment-button.php


v1.0.0-beta.5 - 2018-09-06
--------------------------

##### Reporting Improvements

+ Added a column to the main assignments reporting screen to display the number of ungraded submissions for each assignment
+ On the main assignments screen the total number of submissions is now a link to the submission list for that assignment
+ On assignment submission lists it's now possible to filter assignments by status. This will make it easy to see all assignments which you need to grade on a single screen
+ Both assignment and submission lists will now highlight (in bold) any assignment / submission which is awaiting review / requires a grade.

##### Bug Fixes

+ Assignment uploads will now display their original filenames when uploaded and downloaded
+ Fixed issue causing fatal errors encountered when a quiz is passed on a lesson which has both a quiz and assignment and the assignment is currently imcomplete.


v1.0.0-beta.4 - 2018-06-27
--------------------------

+ Added RTL language support
+ Fixed an issue preventing task deletion for a task that was added to an unsaved assignment
+ Fixed issue requiring permalinks to be manually flushed after plugin installation
+ Fixed an issue preventing the assignments managements sidebar on the course builder to load when adding an assignment to a course created prior to installing assignments
+ Fixed an issue preventing the course builder from loading when the course was affected by the above issue
+ Fixed an issue preventing assignment templates from being overwritten from themes/child themes


v1.0.0-beta.3 - 2018-04-27
--------------------------

+ Fix issue preventing assignment passing grade requirement from preventing lesson completion
+ Use `llms_course_children_post_types` to define assignments as a child of courses


v1.0.0-beta.2 - 2018-04-18
--------------------------

+ Assignments title is required on the builder
+ Added upload and essay assignment types
+ Added assignment grading and reviews
+ Added translation for all strings output via Javascript
+ Improved completed task HTML to display task completed date
+ Ensure that calls to core `is_lifterlms()` return true when viewing single assignment pages

##### Templates Changed

+ templates/assignment/content-tasklist.php
+ templates/assignment/footer.php
+ templates/content-single-assignment-before.php
+ templates/lesson/take-assignment-button.php


v1.0.0-beta.1 - 2018-03-30
--------------------------

+ Initial beta release
