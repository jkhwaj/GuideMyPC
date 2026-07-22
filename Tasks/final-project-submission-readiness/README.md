# תוכנית השלמה, ארגון ובדיקת התאמה לפרויקט הגמר

## הוראת עבודה ל-OpenCode

זהו מסמך העבודה המחייב להכנת `GuideMyPC` להגשה לפי הקובץ **מדריך הפרויקט הסופי**.

OpenCode נדרש:

1. לקרוא את המסמך כולו לפני שינוי קוד.
2. לעבור על **כל קובץ שעוקב אחריו Git**, בלי לדלג על תיקיות, קובצי תיעוד, בדיקות, migrations או קובצי תצורה.
3. לסווג כל קובץ לפני שמחליטים להשאיר, להעביר, למזג, לשכתב או למחוק אותו.
4. לא לבצע "סידור תיקיות" מכני ששובר נתיבים קיימים.
5. לא לטעון שפיצ'ר הושלם לפני שיש קוד עובד, בדיקות וראיות.
6. לעבוד בשלבים קטנים, עם validation אחרי כל שלב.
7. לעדכן את המסמך הזה ואת מסמכי ההגשה כאשר המצב בפועל משתנה.

היעד אינו רק מראה מסודר של המאגר. היעד הוא חבילת הגשה מלאה, ניתנת להרצה ממחשב נקי, תואמת למדריך, ללא סודות וללא טענות לא נכונות.

---

# 1. מקורות סמכות וסדר עדיפויות

כאשר יש סתירה, יש לפעול לפי הסדר הבא:

1. קובץ **מדריך הפרויקט הסופי** של המרצה.
2. התנהגות אמיתית של גרסת הקוד שנבחרה להגשה.
3. `docs/route-contracts.md` ו-`docs/route-inventory.md` לגבי תאימות נתיבים.
4. `docs/project-structure.md` לגבי מבנה runtime.
5. `Tasks/project-structure-migration/README.md` לגבי סדר המיגרציה.
6. המסמך הנוכחי לגבי הכנת ההגשה.
7. תוכניות ישנות יותר, רק כאשר אינן סותרות את הסעיפים לעיל.

## כלל אמינות

הדוח, ה-README, התמונות וה-UML חייבים לתאר רק את ה-release commit שנבדק בפועל.

אין לכתוב שקיימים:

- REST API מלא, אם קיימים רק endpoints חלקיים.
- ניהול ידע מלא, אם אין מסכי CRUD פעילים.
- דוחות, אם אין `admin_reports.php` פעיל ונבדק.
- production deployment, אם נבדקה רק סביבת XAMPP מקומית.
- פיצ'רי AI, uploads או maintenance, אם הם הוסרו או נשארו כ-foundation בלבד.

---

# 2. פסק הדין הנוכחי

## מצב כללי

**הפרויקט אינו מוכן כרגע להגשה סופית.**

תשתית הקוד מתאימה לפרויקט גמר, אבל תוצרי ההגשה הסופיים עדיין חסרים או נמצאים במצב טיוטה.

מדריך הפרויקט מחייב ארבעה סוגי קבצים. החסרה של אחד מהם עלולה לפסול את ההגשה:

1. `Readme.docx` נפרד.
2. דוח סופי ב-Word.
3. קובצי UML, כולל קובץ המקור של כלי השרטוט.
4. קוד המקור המלא, ארוז לפי המבנה הנדרש.

## מטריצת תוצרי חובה

| תוצר | מצב נוכחי | מה נדרש להשלמה |
|---|---|---|
| `Readme.docx` | חסר | ליצור ממקור הצוות, להשלים פרויקט, קבוצה, חברים, פרטי קשר, תפקידים ותרומה |
| `GuideMyPC-Final-Report.docx` | חסר | ליצור דוח עברי מלא עם סעיפים 1-8, תוכן עניינים, מספור עמודים וראיות |
| `GuideMyPC.vpp` | חסר | ליצור פרויקט Visual Paradigm עם ארבע דיאגרמות תואמות ל-release |
| יצואי UML | חסרים | ליצור Use Case, Class, Activity ו-State Machine בפורמט PNG או PDF קריא |
| screenshots | חסרים | לצלם 8-10 תמונות, כולל לפחות שני מסכי מובייל ומצב שגיאה או הרשאה |
| source package | חלקי | ליצור ZIP מה-release commit, לבדוק secrets, לפתוח ולהריץ מתיקייה נקייה |
| outer submission ZIP | חסר | לארוז את ארבעת תוצרי החובה בשמות שהמרצה מאשר |

---

# 3. דרישה מחייבת: מעבר על כל קובץ

OpenCode חייב ליצור בתחילת העבודה את הקובץ:

```text
docs/submission/file-inventory.md
```

הקובץ יכיל שורה לכל קובץ שמוחזר על ידי:

```bash
git ls-files
```

## טבלת inventory מחייבת

| Path | סוג | תפקיד בפועל | מי משתמש בו | החלטה | יעד | בדיקות נדרשות | מצב |
|---|---|---|---|---|---|---|---|
| `example.php` | route | תיאור קצר | forms, routes, JS | keep/move/merge/delete | path יעד | route test | pending |

## סוגי סיווג

כל קובץ יקבל אחד מהסוגים הבאים:

- public route
- compatibility route
- controller
- service
- repository או query object
- security
- bootstrap
- configuration
- view או layout
- public asset
- database migration
- database seed
- database runner
- CLI script
- automated test
- technical documentation
- submission source
- task או implementation plan
- local-only artifact
- generated artifact
- dead-code candidate

## החלטות אפשריות

- `KEEP`: הקובץ נמצא במקום נכון ונדרש.
- `MOVE`: הקובץ נדרש, אך צריך לעבור למיקום יעד לאחר שמירה על contracts.
- `MERGE`: יש חפיפה אמיתית, ויש מקור סמכות יחיד שאפשר ליצור.
- `REWRITE`: המיקום תקין, אך האחריות של הקובץ מעורבבת.
- `DELETE`: אין callers, אין route, אין test, אין דרישת submission ואין שימוש runtime.
- `DEFER`: הקובץ הוא compatibility boundary שאסור להסיר בשלב הנוכחי.

## לפני מחיקה או העברה

חובה לבדוק:

1. callers ו-callees באמצעות code-review-graph.
2. `require`, `include`, autoload ו-route maps.
3. טפסים, redirects וקישורי navigation.
4. JavaScript fetch או form actions.
5. sitemap, robots, reset links ו-canonical URLs.
6. tests שמזכירים את הנתיב או ההתנהגות.
7. documentation והוראות התקנה.
8. Apache `.htaccess`, vhost ו-public front controller.
9. תלות במבנה יחסי של קבצים בתוך חבילת ההגשה.

אין למחוק קובץ רק כי שמו מופיע גם בתיקייה אחרת.

---

# 4. כלי חקירה וסדר עבודה ל-OpenCode

## שלב ראשון, graph

לפני `Grep`, `Glob` או קריאה רחבה של קבצים, יש להשתמש בכלי code-review-graph המוגדרים ב-`AGENTS.md`:

1. `get_architecture_overview`
2. `list_communities`
3. `semantic_search_nodes`
4. `query_graph` עבור callers, callees, imports ו-tests
5. `get_impact_radius`
6. `get_affected_flows`
7. `refactor_tool` עבור מועמדים ל-rename או dead code

## שלב שני, אימות מול Git

לאחר graph:

```bash
git ls-files
git status --short
git diff --check
```

יש להשתמש ב-`rg` או בכלי קריאה רגילים רק כדי להשלים מידע שחסר ב-graph.

## תוצר חקירה לפני שינוי

לפני שינוי מבני, OpenCode יכתוב:

```text
docs/submission/reorganization-plan.md
```

המסמך יכלול:

- מבנה נוכחי.
- מבנה יעד.
- כל move או delete מתוכנן.
- הסיבה לכל שינוי.
- route contracts שעלולים להיפגע.
- migrations שאסור לשנות.
- tests שצריך להוסיף לפני השינוי.
- rollback לכל שלב.

---

# 5. מבנה ה-runtime הרצוי

המבנה הרצוי של מאגר הפיתוח הוא:

```text
GuideMyPC/
|-- app/
|   |-- Core/
|   |-- Security/
|   `-- Features/
|       |-- Pages/
|       |-- Accounts/
|       |-- Guides/
|       |-- Knowledge/
|       |-- Diagnostics/
|       |-- Downloads/
|       |-- Community/
|       |-- Search/
|       |-- Dashboard/
|       |-- Sitemap/
|       `-- Home/
|-- bootstrap/
|   |-- web.php
|   |-- cli.php
|   `-- test.php
|-- config/
|-- public/
|   |-- index.php
|   |-- .htaccess
|   |-- robots.txt
|   `-- assets/
|       |-- css/
|       |-- js/
|       `-- images/
|-- resources/
|   `-- views/
|       |-- layouts/
|       |-- partials/
|       |-- pages/
|       `-- admin/
|-- routes/
|   |-- web.php
|   |-- admin.php
|   `-- api.php
|-- database/
|   |-- migrations/
|   |-- seeds/
|   |-- migrate.php
|   |-- seed.php
|   `-- runner.php
|-- scripts/
|-- tests/
|-- docs/
|   `-- submission/
|-- uml/
|   |-- source/
|   `-- exports/
|-- Tasks/
|-- composer.json
|-- composer.lock
`-- README.md
```

## כללי placement

| תוכן | מיקום |
|---|---|
| classes כלליות | `app/Core/` |
| authentication, authorization, CSRF, sessions | `app/Security/` |
| קוד של feature | `app/Features/<Feature>/` |
| bootstrap לפי context | `bootstrap/` |
| config ללא secrets | `config/` |
| front controller ונכסים ציבוריים | `public/` |
| templates של PHP | `resources/views/` |
| route maps | `routes/` |
| migrations, seeds ו-runners | `database/` |
| פקודות תפעול ואריזה | `scripts/` |
| בדיקות | `tests/` |
| תיעוד | `docs/` |
| UML מקומי | `uml/` |
| תוכניות עבודה | `Tasks/` |

## קובצי PHP בשורש

קובצי `*.php` בשורש הם כרגע compatibility entry points.

אין להעביר או למחוק אותם כפעולת ניקיון בלבד.

אפשר להסיר קובץ route מהשורש רק כאשר:

- הוא מופיע ב-route inventory.
- הנתיב הישן עדיין עובד דרך `public/index.php`.
- form names, query names ו-redirects נשמרו.
- status codes נשמרו.
- session side effects נשמרו.
- route test מכסה guest, user, editor ו-admin לפי הצורך.
- JavaScript, sitemap, email links ו-navigation עודכנו.
- Apache smoke test עבר.

## `.htaccess` כפול

אין למחוק אוטומטית את אחד משני הקבצים:

- `.htaccess` בשורש, הגנה זמנית למבנה compatibility.
- `public/.htaccess`, הגדרת היעד כאשר רק `public/` חשוף.

הסרת קובץ השורש תתבצע רק אחרי שה-public-only document root הוכח בכל סביבת ההרצה הנתמכת.

---

# 6. מבנה חבילת ההגשה הרצוי

חבילת ההגשה אינה חייבת להיות זהה למבנה ה-runtime.

ה-ZIP הסופי צריך להציג את החלוקה שהמרצה ביקש:

```text
GuideMyPC/
|-- frontend/
|   |-- public/assets/
|   `-- resources/views/
|-- backend/
|   `-- runnable PHP source tree
|-- database/
|-- uml/
|-- docs/
|-- README.md
`-- PACKAGE-MANIFEST.txt
```

## כללי אריזה

- `backend/` חייב להישאר runnable עם הנתיבים היחסיים הנדרשים.
- מותר שתהיה כפילות מכוונת בין `frontend/`, `database/` ו-`backend/` בתוך artifact שנוצר אוטומטית.
- אין לערוך ידנית עותקים בתוך `build/`.
- מקור האמת הוא מאגר הפיתוח.
- יש ליצור את החבילה מ-release commit מפורש, לא מ-working tree לא נקי.
- יש לתעד את commit המקור ב-`PACKAGE-MANIFEST.txt`.

## קבצים שאסור לכלול

- `.env`
- credentials או tokens
- `vendor/`, כאשר הוראות ההגשה דורשות source נקי וההתקנה מתבצעת מ-`composer.lock`
- `node_modules/`
- logs
- uploads
- sessions
- cache
- rate-limit data
- database backups
- production data
- `.idea/`
- `.vscode/`
- קובצי כלי מקומיים עם absolute paths
- generated ZIP ישן
- screenshots עם מידע אישי

---

# 7. בדיקת דרישות לפי סעיפי המדריך

## סעיף 0, `Readme.docx`

### מצב

חסר תוצר Word סופי.

קיים מקור Markdown, אך הוא עדיין דורש השלמה ואימות.

### דרישות

- קובץ נפרד מהדוח הראשי.
- שם הפרויקט.
- שם הקבוצה.
- לכל חבר או חברה: שם מלא, תעודת זהות, אימייל וטלפון.
- תפקיד ותחומי אחריות.
- אחוז תרומה, כאשר נדרש.
- עיצוב נקי וברור.

### כלל פרטיות

פרטי קשר מלאים יישמרו בקובץ המקומי הסופי כאשר הקורס דורש אותם. אין לפרסם אותם ב-repository ללא צורך מפורש.

### קובץ יעד מקומי

```text
docs/submission/documents/Readme.docx
```

---

## סעיף 1, חלוקת תפקידים

### מצב

חלקי.

### נדרש

אם הפרויקט בוצע על ידי אדם אחד:

- לציין תרומה של 100 אחוז.
- לפרט Backend.
- Frontend.
- Database.
- Security.
- Testing.
- UML.
- Documentation.
- GitHub וניהול release.

אין לכתוב "הכל" בלבד. צריך לפרט תוצרים קונקרטיים.

---

## סעיף 2, תיאור מילולי של המערכת

### מצב

קיימת טיוטת מקור טובה, אך אין סעיף Word סופי בעברית.

### דרישות

- עד שני עמודים.
- ללא קוד.
- ללא screenshots בתוך סעיף זה.
- תקציר של 5-7 שורות.
- הבעיה או הצורך.
- קהל יעד.
- פיצ'רים מרכזיים שעובדים בפועל.
- ייחודיות או ערך מעשי.
- מגבלות ו-future work מסומנים בבירור.

### טענות מותרות רק לאחר בדיקה

- מדריכים מפורסמים.
- חיפוש.
- חשבונות.
- progress.
- favorites.
- ratings.
- diagnostics שעובדים בפועל.
- downloads שעומדים ב-public policy.
- community לפי המודל הפעיל.
- administration רק למסכים הפעילים והבדוקים.

---

## סעיף 3.1, ארכיטקטורה

### מצב

יש תיאור, אך חסר תרשים סופי.

### תרשים נדרש

```text
Browser
HTML + CSS + JavaScript
        |
        | HTTP / bounded JSON
        v
Apache + public/index.php + legacy route compatibility
        |
        v
PHP 8.2
Bootstrap, Security, Controllers, Services, Repositories, Views
        |
        | prepared mysqli queries
        v
MariaDB 10.4
```

### בדוח יש להסביר

- למה נבחר PHP server-rendered.
- למה נשמרו legacy URLs במהלך המיגרציה.
- למה אין framework או SPA rewrite.
- הפרדה בין public web root לבין private source.
- tradeoffs של הגישה.

---

## סעיף 3.2, Frontend

### מצב

הבסיס קיים.

### לתעד

- HTML5 סמנטי.
- CSS מותאם.
- responsive layout.
- Flexbox ו-Grid כאשר קיימים.
- vanilla JavaScript.
- progressive enhancement.
- fetch או AJAX endpoints שקיימים בפועל.
- validation בצד לקוח, כתוספת בלבד ל-validation בצד שרת.
- dashboard charts באמצעות Chart.js, אם הם פעילים ב-release.

### validation נדרש

- 320px ללא horizontal overflow.
- keyboard navigation.
- focus states.
- error messages.
- reduced motion כאשר רלוונטי.
- desktop, tablet ו-mobile.

---

## סעיף 3.3, Backend

### מצב

הבסיס חזק, אבל אין לטעון ל-REST API מלא אם אינו קיים.

### לתעד

- PHP 8.2.
- Apache.
- sessions.
- authentication.
- capability authorization.
- CSRF.
- validation.
- prepared statements.
- transactions.
- logging ללא secrets.
- safe errors.
- HTTP 303 אחרי mutations כאשר זה החוזה.
- JSON shape רק endpoints שמשתמשים בו בפועל.

### פערים שיש לבדוק

- Knowledge administration.
- Reports route.
- API עבור Categories, Guides, Knowledge, Downloads, Users ו-Reports.
- method guards מלאים.
- route-level guest, user, editor ו-admin tests.

OpenCode צריך להחליט עם בעל הפרויקט אם להשלים את הפערים או להסיר אותם מהיקף הדוח. אסור להשאיר טענה חלקית.

---

## סעיף 3.4, Database

### מצב

קיימים migrations, seeds ו-test database workflow.

### נדרש בדוח

- MariaDB והגרסה שנבדקה.
- הצדקה לבחירה ב-SQL.
- רשימת טבלאות מרכזיות.
- שדות מרכזיים.
- primary keys.
- foreign keys.
- unique constraints.
- one-to-many ו-many-to-many.
- indexes לחיפוש וסינון.
- migration strategy.
- seed strategy.
- test database isolation.

### תוצרים

- ER diagram, בנוסף ל-UML Class Diagram.
- טבלת schema קצרה.

### איסור

אין לשנות, למחוק, לשנות שם או לסדר מחדש migrations היסטוריים שכבר הוחלו.

כל תיקון יהיה migration חדש קדימה.

---

## סעיף 3.5, פלטפורמה

### מצב

Web responsive על Windows ו-XAMPP.

### נדרש

- Windows version.
- XAMPP version.
- Apache version.
- PHP version.
- MariaDB version.
- Browser versions שנבדקו.
- minimum viewport של 320px.
- הסבר למה Web responsive מתאים לקהל היעד.

אין לטעון ל-production deployment עד ש-clean deployment ו-public-root validation עברו.

---

## סעיף 4, screenshots

### מצב

חסר.

### כמות מחייבת

**8-10 screenshots.**

כאשר תוכנית ישנה מבקשת 12-15, יש לעדכן אותה. מדריך ההגשה וה-strict package יקבעו 8-10.

### רשימת צילום מומלצת

1. דף הבית, Guest, desktop.
2. הרשמה, Guest, desktop.
3. התחברות, Guest, desktop.
4. Profile או Dashboard, משתמש בדיקה.
5. Search עם תוצאות וסינון.
6. Guide או Diagnostic flow.
7. Admin Guide CRUD או Category CRUD.
8. מצב unauthorized, validation error או 404.
9. דף הבית ב-320x800.
10. Guide, Search או Admin form ב-320x800.

### לכל תמונה

- filename ברור.
- route.
- role.
- viewport.
- release commit.
- תאריך צילום.
- caption בעברית.
- alt text.
- redaction notes.

### קובץ יעד

```text
docs/submission/screenshots/
```

יש לעדכן את manifest ולהחליף כל `Pending`.

---

## סעיף 5, UML

### מצב

חסר.

### קבצים נדרשים

```text
uml/source/GuideMyPC.vpp
uml/exports/use-case.png או .pdf
uml/exports/class-diagram.png או .pdf
uml/exports/activity-diagram.png או .pdf
uml/exports/state-machine.png או .pdf
```

### 5.1 Use Case

Actors מומלצים:

- Guest.
- Registered User.
- Editor, רק אם פעיל ב-release.
- Administrator.

Use cases יוצגו רק אם זמינים ב-release.

אין לעבור 15-20 use cases בדיאגרמה אחת. ניתן לקבץ לפי subsystem.

### 5.2 Class Diagram

זה חייב להיות UML Class Diagram, לא ERD בלבד.

יש לכלול:

- class name.
- attributes.
- types.
- visibility כאשר מתאים.
- important operations.
- associations.
- composition או aggregation כאשר מוצדק.
- multiplicity.

הדיאגרמה יכולה להיות domain-oriented, אך אסור להמציא methods שלא קיימים או אינם חלק ממודל המערכת.

### 5.3 Activity Diagram

מומלץ לתאר flow אחד מלא, למשל:

```text
User enters problem
-> Search or Diagnostic
-> View options
-> Select guide
-> Follow steps
-> Save progress
-> Complete or escalate
-> Rate result
```

יש לכלול:

- initial node.
- actions.
- decisions.
- validation failure.
- unauthorized או missing record כאשר רלוונטי.
- final node.
- swimlanes כאשר הן מוסיפות בהירות.

### 5.4 State Machine

לבחור אובייקט אחד בלבד עם lifecycle אמיתי.

מועמד טוב הוא `KnowledgeArticle`:

```text
Draft -> Review -> Published -> Archived
Review -> Draft
Archived -> Draft, רק אם המערכת תומכת בזה
```

המעברים חייבים להתאים לקוד ול-schema בפועל.

---

## סעיף 6, GitHub, בונוס

### מצב

קיימת היסטוריית Git ו-Pull Requests.

### נדרש בדוח

- repository URL.
- release commit.
- branches אמיתיים.
- Pull Requests אמיתיים.
- commit history.
- validation checks אמיתיים.
- contributors screenshot, כאשר בטוח לפרסום.

### תיקון תיעוד

יש להסיר מכל מסמך טענה שהפרויקט השתמש רק ב-direct commits אם קיימים PRs ממוזגים.

אין לטעון ל-code review, reviewers או CI שלא התרחשו בפועל.

---

## סעיף 7, ספריות צד שלישי

### מצב

קיימת טיוטת inventory טובה.

### לכל רכיב

- שם.
- גרסה מדויקת.
- קטגוריה.
- מטרה.
- איפה הוא משמש.
- מקור רשמי.
- רישיון.
- delivery method.

### לעדכן לפני הגשה

- PHP.
- Apache.
- MariaDB.
- Composer.
- Git.
- Chart.js.
- Microsoft Word או כלי ה-DOCX ששימש בפועל.
- Visual Paradigm.
- כל browser או כלי צילום שמופיע בדוח.
- כל library נוספת שנמצאת בקוד בפועל.

אין לרשום library רק כי היא מוזכרת במדריך.

---

## סעיף 8, נספחים

### מצב

קיימים מקורות חלקיים.

### אפשר לכלול

- test evidence.
- known limitations.
- ER diagram.
- deployment notes.
- security and privacy notes.
- backup and restore evidence.
- selected code snippets.
- final archive checklist.

אין להוסיף נספחים שמנפחים את הדוח בלי לתרום להבנה.

---

# 8. פערים טכניים שחייבים החלטה

OpenCode חייב לבדוק מחדש כל פער מול ה-release branch הנוכחי.

## פערים ידועים

- Knowledge administration לא הוכח כמושלם.
- `admin_reports.php` לא הוכח כקיים ועובד.
- API מלא למשאבים המרכזיים לא הוכח.
- public-only document root לא הוכח בכל מטריצת Apache.
- private-path probes אינם מלאים.
- authenticated browser tests אינם מלאים.
- clean extraction והתקנה ממחשב או תיקייה נקייה עדיין דורשים ראיה סופית.
- final Word, UML ו-screenshots חסרים.

## החלטה לכל פער

לכל פער יש לבחור אחת משתי אפשרויות:

### A. להשלים

- להוסיף route contract.
- לממש feature מלא.
- להוסיף validation והרשאות.
- להוסיף tests.
- להוסיף screenshots ותיעוד.

### B. להוציא מהיקף ההגשה

- להסיר מה-navigation.
- להסיר calls to action.
- להסיר מה-sitemap.
- להסיר טענות מה-README ומהדוח.
- לסמן Future Work.
- לוודא שאין dead UI שמציג feature לא פעיל.

אין אפשרות שלישית שבה הפיצ'ר מופיע כאילו הושלם אך אינו עובד.

---

# 9. סתירות שחייבים לתקן

## screenshots

מקור הסמכות להגשה הוא 8-10 screenshots.

יש לעדכן כל תוכנית שמבקשת 12-15.

## GitHub history

יש לעדכן מסמכים ישנים שטוענים שאין Pull Requests.

## Class Diagram

יש להבדיל בין:

- ER diagram בסעיף Database.
- UML Class Diagram בסעיף UML.

שניהם יכולים להתבסס על אותו domain, אך הם אינם אותו תוצר.

## README status

אם ה-release מוכן, אין להשאיר כותרת או משפט שאומר שמדובר רק ב-early prototype בלי הקשר.

אם חלקים עדיין prototype, יש לנסח במדויק אילו חלקים.

## main לעומת branch

לפני יצירת ההגשה, יש למזג או לבחור במפורש את branch ההגשה.

אין לבנות מסמכים מ-branch אחד וחבילת source מ-branch אחר.

---

# 10. תוכנית ביצוע ל-OpenCode

## Phase 0, baseline ו-freeze

1. ליצור branch חדש מה-base המאושר, אלא אם ממשיכים branch קיים שאושר.
2. לרשום commit התחלה.
3. להריץ `git status`, `git diff --check` ו-inventory.
4. לתעד versions של PHP, Apache, MariaDB, Composer, Git ו-browser.
5. להריץ את הבדיקות הקיימות ללא שינוי קוד.
6. לתעד failures קיימים.
7. להחליט על feature scope סופי.
8. להקפיא clean URLs, framework changes ופיצ'רים אופציונליים.

### Exit gate

- baseline מתועד.
- test database מזוהה.
- scope מאושר.
- אין working tree לא מוסבר.

---

## Phase 1, מעבר על כל קובץ ותוכנית ארגון

1. ליצור `docs/submission/file-inventory.md`.
2. לסווג כל tracked file.
3. לזהות exact duplicates לפי SHA-256.
4. לזהות repeated basenames.
5. לזהות case-insensitive collisions.
6. לזהות generated artifacts ב-Git.
7. לזהות machine-specific files.
8. לזהות dead code candidates.
9. לזהות root files שאינם routes או metadata.
10. ליצור `docs/submission/reorganization-plan.md`.

### Exit gate

- כל קובץ מופיע ב-inventory.
- לכל delete או move יש evidence.
- לכל שינוי מבני יש tests ו-rollback.

---

## Phase 2, ניקיון בטוח

1. למחוק machine-specific files.
2. להוסיף אותם ל-`.gitignore`.
3. למחוק רק dead code שאין לו callers, routes, tests או submission role.
4. למזג תיעוד כפול רק כאשר נוצר מקור סמכות יחיד וקישורים עודכנו.
5. לא למחוק compatibility routes.
6. לא לשנות migrations היסטוריים.
7. להריץ lint, helper tests, route tests ו-cleanup audit אחרי כל קבוצה.

### Exit gate

- אין exact duplicate לא מכוון.
- אין generated artifact ב-Git.
- אין local absolute path.
- כל test ללא DB עובר.

---

## Phase 3, השלמת קוד חובה

לפי ה-scope שאושר:

1. להשלים CRUD חסר.
2. להשלים הרשאות user, editor ו-admin.
3. להשלים validation ו-CSRF.
4. להשלים pagination, filters ו-sort allowlists.
5. להשלים safe delete rules.
6. להשלים audit events.
7. להשלים dashboard metrics ו-charts שניתן להוכיח.
8. להשלים reports או להסיר אותם מהטענות.
9. להשלים APIs או להסיר את הטענה ל-REST API מלא.
10. לסגור dormant navigation.

### Exit gate

- route matrix מלאה לכל feature שנכלל בדוח.
- tests deterministic.
- אין unpublished data leakage.
- אין privileged action ל-role שגוי.

---

## Phase 4, public root ו-release hardening

1. להגדיר Apache כך שרק `public/` חשוף.
2. לבדוק hostname root ו-subdirectory mode לפי התמיכה שהפרויקט מצהיר עליה.
3. לבדוק כל legacy URL.
4. לבדוק wrong method.
5. לבדוק 404, 403, 419, 429 ו-500.
6. לבדוק `.env`, `app/`, `bootstrap/`, `config/`, `database/`, `scripts/`, `tests/`, `Tasks/`, `docs/` ו-Composer metadata.
7. להריץ fresh migration.
8. להריץ representative upgrade.
9. להריץ migration שוב ולצפות ל-zero new migrations.
10. להריץ seed פעמיים ולוודא idempotence.
11. לבצע backup ו-restore rehearsal.

### Exit gate

- רק `public/` נגיש.
- כל private path חסום.
- clean install עובד.
- rollback מתועד.

---

## Phase 5, מסמכי הגשה

1. להשלים מקור צוות.
2. ליצור `Readme.docx`.
3. להשלים דוח עברי.
4. ליצור תוכן עניינים.
5. להוסיף מספור עמודים.
6. ליצור architecture diagram.
7. ליצור ER diagram.
8. ליצור `GuideMyPC.vpp`.
9. לייצא ארבע דיאגרמות UML.
10. לצלם 8-10 screenshots.
11. לעדכן third-party inventory.
12. לעדכן test evidence ל-release commit.
13. לעדכן known limitations.
14. לעדכן README כך שישקף את ה-release.

### Exit gate

- כל מסמך נפתח תקין.
- כל diagram קריא.
- כל screenshot עבר redaction review.
- כל טענה תואמת לקוד.

---

## Phase 6, אריזה ואימות סופי

1. ליצור release commit.
2. לוודא working tree נקי.
3. להריץ fast gate.
4. להריץ full gate עם test database.
5. להריץ strict package מה-release commit.
6. לפתוח את ה-ZIP.
7. לבדוק את המבנה.
8. לחפש secrets ו-PII.
9. לחלץ לתיקייה נקייה.
10. לבצע installation רק לפי ההוראות שב-ZIP.
11. להריץ migration, seed ו-verification מהחילוץ.
12. לפתוח את Word, VPP, exports ו-screenshots.
13. ליצור outer ZIP לפי דרישת המרצה.
14. לבצע independent review.

### Exit gate

- ארבעת סוגי הקבצים קיימים.
- clean extraction עובדת.
- אין secrets.
- אין קובץ חסר.
- אין טענה לא נכונה בדוח.

---

# 11. פקודות validation

יש להתאים את הנתיבים ל-Windows/XAMPP כאשר נדרש.

## Fast checks

```powershell
composer validate --strict
composer install --no-interaction
composer run verify:fast
php scripts/audit-repository-cleanup.php
git diff --check
```

## Database checks

```powershell
C:\xampp\php\php.exe database\migrate.php --database=guidemypc_test
C:\xampp\php\php.exe database\seed.php --database=guidemypc_test
C:\xampp\php\php.exe database\migrate.php --database=guidemypc_test
C:\xampp\php\php.exe database\seed.php --database=guidemypc_test
composer run verify
```

## Package check

```powershell
php scripts/package-submission.php `
  --commit=<FINAL_COMMIT> `
  --output=build/<GROUP>_GuideMyPC.zip `
  --strict `
  --force
```

## חיפושי בטיחות בתוך החבילה

```powershell
# דוגמאות, יש להתאים לסביבה
Get-ChildItem -Recurse -Force <EXTRACTED_PACKAGE>
Select-String -Path <EXTRACTED_PACKAGE>\* -Pattern "DB_PASSWORD|API_KEY|BEGIN PRIVATE KEY" -Recurse
```

אין להסתמך רק על שם קובץ. יש לבדוק גם תוכן של configs, docs, logs ו-screenshots.

---

# 12. בדיקות ידניות מחייבות

## Actors

- Guest.
- Registered User.
- Editor, אם נכלל ב-release.
- Administrator.

## Flows

- home.
- registration.
- login success.
- invalid login.
- logout.
- profile.
- settings.
- search.
- guide read.
- save progress.
- favorite.
- rating.
- diagnostic.
- downloads.
- community.
- category CRUD.
- guide CRUD.
- knowledge CRUD, אם נכלל.
- reports, אם נכלל.
- API, אם נכלל.

## Errors

- invalid CSRF.
- unauthorized.
- forbidden.
- missing record.
- invalid form data.
- duplicate slug.
- wrong method.
- database failure.
- 404.
- 500 safe behavior.
- rate limit.

## Responsive

- 1440x900.
- tablet width.
- 320x800.
- zoom או reflow לפי דרישת accessibility.

לכל בדיקה יש לרשום:

- date.
- tester.
- commit.
- environment.
- route.
- role.
- expected result.
- actual result.
- status.
- evidence reference.

---

# 13. קבצים ידועים שדורשים עדכון

OpenCode חייב לבדוק אותם, ולא להניח שהרשימה מלאה.

## `README.md`

- להחליף ניסוח ישן שאינו תואם למצב ה-release.
- לוודא שהתקנה ממחשב נקי מתועדת.
- לתעד package commands.
- לתעד public-root configuration.
- לתעד limitations אמיתיים.

## `docs/submission/report-outline.md`

- לעדכן היסטוריית Git ו-Pull Requests.
- להפוך את המתווה למקור אמת לדוח העברי.
- לא לטעון לפיצ'רים שלא הושלמו.

## `docs/submission/system-overview.md`

- לעדכן ל-release commit.
- לוודא שהטקסט עד שני עמודים לאחר העברה ל-Word.

## `docs/submission/third-party-inventory.md`

- לרענן versions.
- להוסיף Word ו-Visual Paradigm.
- להסיר כלי שלא שימש בפועל.

## `docs/submission/test-evidence.md`

- להחליף release-candidate ישן ב-release commit.
- להוסיף clean install, package, route matrix, accessibility ו-private-path evidence.

## `docs/submission/screenshots/README.md`

- להחליף כל `Pending`.
- לשמור 8-10 שורות בפועל.

## `docs/team/README.md`

- להשלים תפקידים ותוצרים.
- לא לפרסם PII מעבר למה שמדיניות הקורס דורשת.

## `Tasks/final-project-mvp/README.md`

- לעדכן 12-15 screenshots ל-8-10.
- לעדכן statuses לפי המציאות.
- להסיר רשימות פיצ'רים שלא ייכללו ב-release.

## `docs/project-structure.md`

- להפריד בבירור בין current, transitional ו-target.
- לא לכתוב שמיגרציה הושלמה אם compatibility code עדיין פעיל.

## `docs/route-contracts.md` ו-`docs/route-inventory.md`

- לכל route חדש יש להוסיף contract לפני implementation.
- לכל route שהוסר יש לתעד replacement ותאימות.

## `composer.json`

- לשמור `verify:fast` ו-`verify`.
- להוסיף package commands רק אם הסקריפטים קיימים ונבדקו.
- לא להוסיף dependency שאינו בשימוש.

## `.gitignore`

- להגן על final local documents.
- להגן על UML source/exports המקומיים כאשר אינם מיועדים ל-Git.
- להגן על screenshots, build, release, logs, backups, IDE ו-machine configs.

---

# 14. כללי מחיקה ומיזוג

## מותר למחוק כאשר כל התנאים מתקיימים

- אין route map.
- אין caller.
- אין include או autoload.
- אין form, redirect או JS reference.
- אין test.
- אין documentation requirement.
- אין שימוש בתהליך ההגשה.
- אין runtime behavior נסתר.
- cleanup audit ו-full tests עוברים לאחר המחיקה.

## אסור למחוק אוטומטית

- root compatibility routes.
- `config.php` כל עוד callers קיימים.
- selected `includes/` wrappers כל עוד callers קיימים.
- historical migrations.
- route maps.
- root `.htaccess` לפני public-root sign-off.
- `public/.htaccess`.
- README של תיקייה כאשר הוא מסביר artifact מקומי או workflow.
- tests רק כי הם נראים דומים.

## מיזוג קבצים

מיזוג מותר רק כאשר:

- האחריות זהה.
- אין צורך בשתי שכבות compatibility.
- callers יכולים לעבור בשלבים.
- source of truth חדש מתועד.
- duplicated behavior נבדק לפני ואחרי.

---

# 15. Definition of Done

הפרויקט מוכן להגשה רק כאשר כל הסעיפים הבאים מסומנים:

## קוד והרצה

- [ ] ה-release commit נבחר ומתועד.
- [ ] `composer install` עובד מ-`composer.lock`.
- [ ] PHP lint עובר לכל tracked PHP files.
- [ ] fast verification עובר.
- [ ] full verification עובר על DB שמסתיים ב-`_test`.
- [ ] fresh migrations עוברים.
- [ ] representative upgrade עובר.
- [ ] second migration run הוא no-op.
- [ ] seed חוזר ללא כפילויות מזיקות.
- [ ] route matrix עוברת.
- [ ] public-only root נבדק.
- [ ] private paths חסומים.
- [ ] clean extraction עובדת.

## מסמכים

- [ ] `Readme.docx` קיים ונפתח.
- [ ] דוח Word קיים ונפתח.
- [ ] הדוח בעברית תקינה.
- [ ] סעיפים 1-8 קיימים וממוספרים.
- [ ] תוכן עניינים קיים.
- [ ] מספור עמודים קיים.
- [ ] תיאור המערכת אינו עובר שני עמודים.
- [ ] כל בחירה טכנולוגית כוללת הצדקה.
- [ ] כל version מדויקת.
- [ ] כל טענה תואמת ל-release.

## UML וראיות

- [ ] `GuideMyPC.vpp` קיים ונפתח.
- [ ] Use Case export קריא.
- [ ] Class Diagram export קריא.
- [ ] Activity Diagram export קריא.
- [ ] State Machine export קריא.
- [ ] ER diagram קיים בדוח או בנספח.
- [ ] קיימים 8-10 screenshots בלבד.
- [ ] לפחות שני screenshots הם mobile.
- [ ] קיים error או permission state.
- [ ] כל screenshot כולל caption ומספור.
- [ ] screenshots אינם חושפים מידע אישי או secrets.

## אריזה

- [ ] source package נוצר מ-release commit.
- [ ] `frontend/` קיים.
- [ ] `backend/` קיים ורץ.
- [ ] `database/` קיים.
- [ ] `uml/` קיים.
- [ ] `docs/` קיים.
- [ ] `README.md` נמצא בשורש החבילה.
- [ ] `.env` אינו בחבילה.
- [ ] `node_modules/` אינו בחבילה.
- [ ] `vendor/` מטופל לפי strategy מתועדת.
- [ ] logs, uploads, backups ו-IDE files אינם בחבילה.
- [ ] outer ZIP כולל את כל ארבעת תוצרי החובה.
- [ ] reviewer נוסף פתח ובדק את ה-ZIP.

---

# 16. פורמט דיווח של OpenCode

בסיום כל phase, OpenCode יחזיר:

```text
Phase:
Branch:
Start commit:
End commit:
Files inspected:
Files changed:
Files moved:
Files merged:
Files deleted:
Routes affected:
Migrations added:
Tests added or updated:
Commands run:
Pass results:
Fail results:
Manual evidence:
Known blockers:
Rollback:
Next phase:
```

אין להשתמש בניסוחים כמו "כנראה עובד" או "נראה תקין" ללא פקודה, בדיקה או ראיה.

---

# 17. הוראת פתיחה קצרה שניתן לתת ל-OpenCode

```text
Read AGENTS.md and Tasks/final-project-submission-readiness/README.md in full.
Treat the final-project guide as the primary authority.
First create a tracked-file inventory using git ls-files and classify every file.
Do not move or delete compatibility routes until their route contracts and tests prove parity.
Reorganize the runtime source toward the documented target structure, and generate the required frontend/backend/database/uml/docs layout only as a submission artifact.
Resolve every gap, contradiction, duplicate, dead file, missing test, missing document, UML item, screenshot and package requirement described in the plan.
Work phase by phase, run the required checks after each phase, update the evidence documents, and do not mark the project ready until the strict package and clean-extraction gates pass.
```

---

# 18. מצב התוכנית

- Status: Ready for detailed inventory and implementation.
- Submission readiness: Blocked until all Definition of Done items pass.
- Runtime reorganization: Must remain compatibility-first.
- Final package layout: `frontend/`, `backend/`, `database/`, `uml/`, `docs/`.
- Mandatory screenshot count: 8-10.
- Mandatory UML count: 4.
- Mandatory submission file types: 4.
