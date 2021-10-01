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
    [x] Identify who an admin is by adding an `is_admin` attribute to the users table.
    [x] Show hidden and unapproved offices when filtering by `user_id` and the auth user matches the user so hosts can see all their listings
    [xx] Change the user_id filter to visitor_id and host_id to user_id
    [xx] Switch to using Custom Polymorphic Types
    [xx] Configure the resuources
    
    ## Show office
    
    [x] Show count of previous reservations
    [x] Include tags, images and user
    
    ## Create Office Endpoint
    
    [x] Host must be authenticated and email verified
    [x] Token (if exists) must allow office.create
    [x] Cannot fill 'approval_status'
    [x] Store inside a database transaction 
    [x] Validation
        - Cannot fill `approval_status`
    [x] Notify admin on new office
    

    ## Update Office Endpoint
    [x] Must be authenticated and email verified
    [x] Token (if exists) must allow office.update
    [x] Validation
    [x] Can only update their own offices
        - Cannot update `approval_status`
    [x] Mark as pending when critical attributes are updated and notify admin**
    
    ## Delete Office Endpoint
    
    [x] Must be authenticated & email verified
    [x] Token (if exists) must allow office.delete
    [x] Can only delete their own offices
    [x] Cannot delete an office that has a reservation
    
    ## Office Photos

    [x] Attaching photos to an office
    [] Allow choosing a photo to become the featured photo
    [] Deleting a photo
        - Must have at least one photo if it is approved.

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

    ## Make Reservations Enpoint
    
    [ ] Must be authenticated & email verified
    [ ] Token (if exists) must allow `reservations.make`
    [ ] Cannot make reservations on their own property
    [ ] Validate no other reservation conflicts with the same time
    [ ] Use locks to make the process atomic
    [ ] Email user & host when a reservation is made
    [ ] Email user & host on reservation start day
    [ ] Generate WIFI password for new reservations (store encrypted)

    ## Cancel Reservation Endpoint

    [ ] Must be authenticated & email verified
    [ ] Token (if exists) must allow `reservations.cancel`
    [ ] Can only cancel their own reservation
    [ ] Can only cancel an active reservation that has a start_date in the future

[] Handle Billing with Cashier

local scopes
factorystates