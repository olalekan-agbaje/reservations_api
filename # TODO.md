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
    
    ## List offices
    
    [x] Show only approved and visible records
    [x] Filter by hosts
    [x] Filter by users
    [x] Include tags, images and user
    [x] Show count of previous reservations
    [x] Paginate
    [x] Sort by distance if lng/lat proficed. Otherwise, sort by oldest first.
    [x] Order by distance but don't include the distance attribute
    [xx] Change the user_id filter to visitor_id and host_id to user_id
    [xx] Switch to using Custom Polymorphic Types
    [xx] Configure the resuources
    
    ## Show office
    
    [x] Show count of previous reservations
    [x] Include tags, images and user
    
    ## Create office
    
    [] Host must be authenticated and email verified
    [] Token (if exists) must allow office.create
    [] Cannot fill 'approval_status'
    [] Attach photos to offices endpoint
    [] Validation
    
    ## Delete Office Endpoint
    
    [] Must be authenticated & email verified
    [] Token (if exists) must allow office.delete
    [] Can only delete their own offices
    [] Cannot delete an office that has a reservation

[] Reservations
    ## List Reservtion Endpoint
    [] Must be authenticated & email verified
    [] Token (if exists) must allow reservations.show
    [] Can only list their own reservations or reservations on their own offices
    [] Allow filtering by office_id    
    [] Allow filtering by user_id    
    [] Allow filtering by date range    
    [] Allow filtering by status   
    [] Paginate


local scopes
factorystates