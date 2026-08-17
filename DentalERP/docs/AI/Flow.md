# AI Engine Flow Diagrams

## Flow 1: Submit AI Query

```
  User
   │
   ▼
┌─────────────────┐
│ POST /api/v1/   │
│ ai-queries      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ StoreAIRequest  │── Validate: query_type, prompt, model (optional)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIController    │── Extract org_id from auth()->user()->organization_id
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIService       │── DB::transaction
│ ::create()      │── CreateAIDTO.toArray() includes status='pending'
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIRepository    │── AI::create(data)
│ ::create()      │── HasAudit sets created_by
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ 201 Created     │── AIResource
│ AI Query        │── status: pending
└─────────────────┘
```

## Flow 2: Process AI Query (Background)

```
  System / Queue Worker
   │
   ▼
┌─────────────────┐
│ Pick pending    │── AI::where('status','pending')
│ query           │── ->where('organization_id', org_id)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Transition to   │── status = 'processing'
│ processing      │── DB::transaction
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Execute AI      │── Call AI model API
│ inference       │── Send prompt + model
└────────┬────────┘
         │
    ┌────┼────┐
    │         │
    ▼         ▼
 Success    Failure
    │         │
    ▼         ▼
┌────────┐ ┌────────┐
│completed│ │ failed │
│tokens  │ │ error  │
│response│ │ msg    │
└────────┘ └────────┘
```

## Flow 3: List AI Queries

```
  User
   │
   ▼
┌─────────────────┐
│ GET /api/v1/    │
│ ai-queries      │── ?query_type= &status= &date_from= &date_to= &per_page= &page=
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIController    │── Extract org_id, filters from request
│ ::index()       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIService       │── Pass filters to repository
│ ::paginate()    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIRepository    │── AI::where('organization_id', org_id)
│ ::paginate()    │── Apply whitelisted filters
│                 │── AIResource::collection()
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ 200 OK          │── Paginated AIResource collection
│                 │── Prompt truncated at 200 chars
└─────────────────┘
```

## Flow 4: Retry Failed Query

```
  User
   │
   ▼
┌─────────────────┐
│ POST /api/v1/   │
│ ai-queries/{id} │
│ /retry          │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIController    │── Extract org_id from auth
│ ::retry()       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIService       │── findById(id, org_id)
│ ::retry()       │── Check status === failed
└────────┬────────┘
         │
    ┌────┼────┐
    │         │
    ▼         ▼
  failed    not failed
    │         │
    ▼         ▼
┌────────┐ ┌──────────────┐
│pending │ │BusinessException│
│error=  │ │"Only failed    │
│null    │ │queries can be  │
│        │ │retried"        │
└────────┘ └──────────────┘
    │
    ▼
┌─────────────────┐
│ 200 OK          │── Updated AIResource
└─────────────────┘
```

## Flow 5: Cancel Query

```
  User
   │
   ▼
┌─────────────────┐
│ POST /api/v1/   │
│ ai-queries/{id} │
│ /cancel         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIController    │── Extract org_id from auth
│ ::cancel()      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ AIService       │── findById(id, org_id)
│ ::cancel()      │── Check status ∈ {pending, processing}
└────────┬────────┘
         │
    ┌────┼────┐
    │         │
    ▼         ▼
pending/    completed/
processing   failed
    │         │
    ▼         ▼
┌────────┐ ┌──────────────┐
│failed  │ │BusinessException│
│error=  │ │"Only pending or│
│"Cancel │ │processing     │
│led by  │ │queries can be │
│user"   │ │cancelled"     │
└────────┘ └──────────────┘
    │
    ▼
┌─────────────────┐
│ 200 OK          │── Updated AIResource
└─────────────────┘
```