# TODO
[x] Prepare migrations
[x] Seed the initial tags
[x] Prepare models
[x] Prepare factories
[x] Prepare resources
[x] Tags
    [x] Routes
    [x] Controller
    [x] Tests
[] Offices
    [] List offices
        [x] Show only approved and visible records
        [x] Filter by hosts
        [x] Filter by users
        [x] Include tags, images and user
        [x] Show count of previous reservations
        [x] Paginate
        [x] Sort by distance if lng/lat proficed. Otherwise, sort by oldest first.
    [] Show office
        [x] Show count of previous reservations
        [x] Include tags, images and user
    [] Create office
        [] Host must be authenticated and email verified
        [] Cannot fill 'approval_status'
        [] Attach photos to offices endpoint
        


local scopes
factorystates