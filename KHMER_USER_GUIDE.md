# មគ្គុទ្ទេសក៍ប្រើប្រាស់ប្រព័ន្ធបណ្ណាល័យ KHLIBRARY

## ស្ថាបត្យកម្មប្រព័ន្ធ

```mermaid
graph TB
    A[អ្នកប្រើប្រាស់] --> B[ទំព័រវែប]
    B --> C[ម៉ាស៊ីនមេ Apache]
    C --> D[PHP Router]
    D --> E[Controller]
    E --> F[Service]
    F --> G[Model]
    G --> H[មូលដ្ឋានទិន្នន័យ MySQL]
    E --> I[View - ទំព័រ PHP]
    I --> B
```

## ទំនាក់ទំនងតារាងទិន្នន័យ

```mermaid
erDiagram
    USERS ||--o{ LOANS : "បង្កើត"
    BOOKS ||--o{ LOANS : "ខ្ចីក្នុង"
    BOOKS }o--|| CATEGORIES : "ជាប់ទៅ"
    BOOKS }o--o{ AUTHORS : "សរសេរដោយ"

    USERS {
        int user_id
        string email
        string password_hash
        string first_name
        string last_name
        enum user_type
        enum status
    }

    BOOKS {
        int book_id
        string isbn
        string title
        text description
        int category_id
        int total_quantity
        int available_quantity
        string cover_image
    }

    LOANS {
        int loan_id
        int user_id
        int book_id
        date loan_date
        date due_date
        date return_date
        enum status
    }

    CATEGORIES {
        int category_id
        string name
        text description
    }

    AUTHORS {
        int author_id
        string first_name
        string last_name
        text biography
    }
```

## លំហូរការងារចូលប្រើប្រាស់

```mermaid
sequenceDiagram
    participant អ្នកប្រើ
    participant ប្រព័ន្ធ
    participant មូលដ្ឋាន

    អ្នកប្រើ->>ប្រព័ន្ធ: ចូលទៅកាន់គេហទំព័រ
    ប្រព័ន្ធ->>ប្រព័ន្ធ: ពិនិត្យ Session
    
    alt មិនទាន់ចូល
        ប្រព័ន្ធ-->>អ្នកប្រើ: បង្ហាញទំព័រ Login
        អ្នកប្រើ->>ប្រព័ន្ធ: បញ្ចូល Email និង Password
        ប្រព័ន្ធ->>មូលដ្ឋាន: ពិនិត្យគណនី
        មូលដ្ឋាន-->>ប្រព័ន្ធ: ទិន្នន័យអ្នកប្រើ
        ប្រព័ន្ធ->>ប្រព័ន្ធ: បង្កើត Session
        ប្រព័ន្ធ-->>អ្នកប្រើ: ចូលប្រើប្រាស់បានជោគជ័យ
    else ចូលរួចហើយ
        ប្រព័ន្ធ-->>អ្នកប្រើ: បង្ហាញទំព័រសៀវភៅ
    end
```

## លំហូរការខ្ចីសៀវភៅ

```mermaid
flowchart TD
    A[អ្នកប្រើរកសៀវភៅ] --> B{មានសៀវភៅ?}
    B -->|មាន| C[ចុចប៊ូតុង ខ្ចី]
    B -->|អស់| D[បង្ហាញស្ថានភាព អស់]
    
    C --> E[ស្នើសុំខ្ចី]
    E --> F[រង់ចាំការអនុម័ត]
    
    F --> G{Admin ពិនិត្យ}
    G -->|អនុម័ត| H[កំណត់កាលបរិច្ឆេទត្រឡប់]
    G -->|បដិសេធ| I[ជូនដំណឹងបដិសេធ]
    
    H --> J[ធ្វើបច្ចុប្បន្នភាពចំនួន]
    J --> K[បង្កើតការខ្ចីសកម្ម]
    K --> L[ជូនដំណឹងអនុម័ត]
    
    L --> M[អ្នកប្រើមានសៀវភៅ]
    M --> N{ត្រឡប់សៀវភៅ?}
    
    N -->|បាទ/ចាស| O[ចុចប៊ូតុង ត្រឡប់]
    N -->|ទេ| P{ពិនិត្យកាលបរិច្ឆេទ}
    
    O --> Q[ដំណើរការត្រឡប់]
    Q --> R[ធ្វើបច្ចុប្បន្នភាពចំនួន]
    R --> S[គណនាពិន័យ ប្រសិនយឺត]
    S --> T[សម្គាល់ជាត្រឡប់រួច]
    
    P -->|យឺត| U[គណនាពិន័យប្រចាំថ្ងៃ]
    P -->|មិនទាន់ដល់| M
```

## មុខងារសម្រាប់អ្នកប្រើប្រាស់ធម្មតា

```mermaid
flowchart LR
    A[ចូលប្រព័ន្ធ] --> B[រកមើលសៀវភៅ]
    B --> C[ស្វែងរក & ច្រោះ]
    C --> D[មើលព័ត៌មានលម្អិត]
    D --> E[ស្នើសុំខ្ចី]
    E --> F[មើលប្រវត្តិខ្ចី]
    F --> G[ត្រឡប់សៀវភៅ]
    G --> H[ចាកចេញ]
```

## មុខងារសម្រាប់ Admin

```mermaid
flowchart TB
    A[ចូលប្រព័ន្ធ Admin] --> B[Dashboard]
    B --> C[គ្រប់គ្រងសៀវភៅ]
    B --> D[គ្រប់គ្រងអ្នកប្រើ]
    B --> E[គ្រប់គ្រងការខ្ចី]
    
    C --> C1[បន្ថែមសៀវភៅថ្មី]
    C --> C2[កែប្រែសៀវភៅ]
    C --> C3[លុបសៀវភៅ]
    
    D --> D1[បង្កើតអ្នកប្រើថ្មី]
    D --> D2[កែប្រែអ្នកប្រើ]
    D --> D3[លុបអ្នកប្រើ]
    
    E --> E1[អនុម័តការខ្ចី]
    E --> E2[បដិសេធការខ្ចី]
    E --> E3[ដំណើរការត្រឡប់]
    E --> E4[មើលការខ្ចីយឺត]
```

## ស្ថានភាពការខ្ចីសៀវភៅ

```mermaid
stateDiagram-v2
    [*] --> Pending: ស្នើសុំខ្ចី
    Pending --> Borrowed: Admin អនុម័ត
    Pending --> Rejected: Admin បដិសេធ
    Borrowed --> Returned: ត្រឡប់សៀវភៅ
    Borrowed --> Overdue: លើសកាលកំណត់
    Overdue --> Returned: ត្រឡប់សៀវភៅ + ពិន័យ
    Rejected --> [*]
    Returned --> [*]
```

## ការប្រើប្រាស់ API

```mermaid
sequenceDiagram
    participant Frontend
    participant API
    participant Database

    Frontend->>API: POST /api/v1/loans/request
    Note over Frontend,API: {book_id: 10}
    
    API->>Database: ពិនិត្យសៀវភៅមាន
    Database-->>API: ទិន្នន័យសៀវភៅ
    
    API->>Database: បង្កើតការខ្ចី
    Database-->>API: loan_id: 15
    
    API-->>Frontend: Success Response
    Note over API,Frontend: {success: true, loan_id: 15}
    
    Frontend->>Frontend: បង្ហាញសារជោគជ័យ
```

## ការពិនិត្យសុវត្ថិភាព

```mermaid
flowchart TD
    A[ស្នើសុំចូលមក] --> B{មាន Session?}
    B -->|ទេ| C[Redirect ទៅ Login]
    B -->|មាន| D{Session ត្រឹមត្រូវ?}
    D -->|ទេ| C
    D -->|បាទ| E{មានសិទ្ធិ?}
    E -->|ទេ| F[បង្ហាញ 403 Forbidden]
    E -->|មាន| G[អនុញ្ញាតចូលប្រើ]
    G --> H[ដំណើរការស្នើសុំ]
```

## សេចក្តីសង្ខេប

### សម្រាប់អ្នកប្រើប្រាស់ធម្មតា:
1. **ចូលប្រព័ន្ធ** - ប្រើ Email និង Password
2. **រកសៀវភៅ** - ស្វែងរកតាមចំណងជើង អ្នកនិពន្ធ ឬប្រភេទ
3. **ស្នើសុំខ្ចី** - ចុចប៊ូតុង "ខ្ចី" លើសៀវភៅដែលចង់បាន
4. **រង់ចាំអនុម័ត** - Admin នឹងពិនិត្យនិងអនុម័ត
5. **ត្រឡប់សៀវភៅ** - ចុចប៊ូតុង "ត្រឡប់" នៅក្នុង Profile

### សម្រាប់ Admin:
1. **ចូលប្រព័ន្ធ** - ប្រើគណនី Admin
2. **គ្រប់គ្រងសៀវភៅ** - បន្ថែម កែប្រែ លុបសៀវភៅ
3. **គ្រប់គ្រងអ្នកប្រើ** - បង្កើត កែប្រែ លុបអ្នកប្រើ
4. **អនុម័តការខ្ចី** - ពិនិត្យនិងអនុម័តការស្នើសុំ
5. **តាមដានការខ្ចីយឺត** - មើលនិងគ្រប់គ្រងការខ្ចីយឺត

---

**ទំនាក់ទំនង**: https://t.me/eirsvi  
**កំណែ**: 2.0  
**ថ្ងៃបច្ចុប្បន្នភាព**: ១៨ វិច្ឆិកា ២០២៥
