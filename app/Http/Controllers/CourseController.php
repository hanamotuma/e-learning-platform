<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\ProgressTracking;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use App\Models\Notification;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['category', 'instructor'])
            ->where('is_published', true);
        
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }
        
        switch ($request->sort) {
            case 'popular':
                $query->withCount('enrollments')->orderBy('enrollments_count', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
        }
        
        $courses = $query->paginate(12);
        $categories = Category::all();
        
        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'difficulty', 'sort']),
        ]);
    }
    
    public function show($id)
    {
        // Try to find by ID first
        $course = Course::with(['category', 'instructor', 'sections.lessons'])
            ->find($id);
        
        // If not found by ID, try by slug
        if (!$course) {
            $course = Course::with(['category', 'instructor', 'sections.lessons'])
                ->where('slug', $id)
                ->first();
        }
        
        // If still not found, return the correct sample course data
        if (!$course) {
            $courseData = $this->getCourseDataById($id);
            
            return Inertia::render('Course/Show', [
                'course' => $courseData,
                'courseCurriculum' => $courseData['uniqueCurriculum'] ?? [],
                'courseReviews' => $courseData['uniqueReviews'] ?? [],
                'isEnrolled' => false,
                'enrollment' => null,
                'progress' => null,
                'relatedCourses' => [],
            ]);
        }
        
        $isEnrolled = false;
        $enrollment = null;
        $progress = null;
        
        if (Auth::check()) {
            $enrollment = Enrollment::where('user_id', Auth::id())
                ->where('course_id', $course->id)
                ->first();
            $isEnrolled = $enrollment && $enrollment->status !== 'dropped';
        }
        
        return Inertia::render('Course/Show', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'price' => $course->price,
                'originalPrice' => $course->original_price ?? $course->price * 3,
                'image' => $course->image ?? 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=500',
                'rating' => $course->rating ?? 4.8,
                'reviews' => $course->reviews_count ?? 0,
                'students' => $course->students_count ?? 0,
                'hours' => $course->duration_hours ?? 40,
                'instructor' => $course->instructor ? $course->instructor->name : 'Expert Instructor',
                'category' => $course->category ? $course->category->name : 'Development',
                'level' => $course->difficulty_level ?? 'All Levels',
                'badge' => $course->badge ?? 'Featured',
                'what_you_will_learn' => $course->what_you_will_learn,
                'sections' => $course->sections ?? [],
            ],
            'courseCurriculum' => $this->getCurriculumForCourse($course->id),
            'courseReviews' => $this->getReviewsForCourse($course->id),
            'isEnrolled' => $isEnrolled,
            'enrollment' => $enrollment,
            'progress' => $progress,
            'relatedCourses' => [],
        ]);
    }

    private function getCourseDataById($id)
    {
        $courses = [
            1 => [
                'id' => 1,
                'title' => 'The Complete Web Development Bootcamp 2026',
                'instructor' => 'Dr. Angela Yu',
                'price' => 4990,
                'originalPrice' => 19990,
                'rating' => 4.8,
                'reviews' => 12450,
                'students' => 125000,
                'hours' => 45,
                'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=500',
                'category' => 'development',
                'badge' => 'Bestseller',
                'level' => 'Beginner to Advanced',
                'description' => 'Learn web development from scratch with this comprehensive bootcamp. Master HTML, CSS, JavaScript, React, Node.js, and more.',
                'what_you_will_learn' => "Build full-stack web applications\nMaster modern JavaScript frameworks\nDeploy applications to production\nWork with databases and APIs\nCreate responsive designs\nImplement authentication and security",
            ],
            2 => [
                'id' => 2,
                'title' => 'Advanced UI/UX Design Masterclass',
                'instructor' => 'Sarah Johnson',
                'price' => 3990,
                'originalPrice' => 14990,
                'rating' => 4.9,
                'reviews' => 8450,
                'students' => 45000,
                'hours' => 32,
                'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=500',
                'category' => 'design',
                'badge' => 'Top Rated',
                'level' => 'Intermediate',
                'description' => 'Master UI/UX design principles and create stunning user interfaces that users love.',
                'what_you_will_learn' => "Design systems thinking\nUser research methods\nPrototyping with Figma\nUsability testing\nWireframing techniques\nInteraction design",
            ],
            3 => [
                'id' => 3,
                'title' => 'Artificial Intelligence A-Z 2026',
                'instructor' => 'Prof. Andrew Ng',
                'price' => 5990,
                'originalPrice' => 24990,
                'rating' => 4.9,
                'reviews' => 15600,
                'students' => 89000,
                'hours' => 52,
                'image' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=500',
                'category' => 'data',
                'badge' => 'Trending',
                'level' => 'All Levels',
                'description' => 'Learn AI, machine learning, and deep learning from the world\'s leading expert.',
                'what_you_will_learn' => "Machine learning algorithms\nNeural networks\nDeep learning\nNatural language processing\nComputer vision\nReinforcement learning",
            ],
            4 => [
                'id' => 4,
                'title' => 'Digital Marketing & Growth Hacking',
                'instructor' => 'Gary Vee',
                'price' => 3490,
                'originalPrice' => 12990,
                'rating' => 4.7,
                'reviews' => 9820,
                'students' => 67000,
                'hours' => 28,
                'image' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8f2c293?w=500',
                'category' => 'marketing',
                'badge' => 'Popular',
                'level' => 'Beginner',
                'description' => 'Master digital marketing strategies, social media growth, SEO, and conversion optimization.',
                'what_you_will_learn' => "Social media marketing\nSEO optimization\nEmail marketing\nContent strategy\nAnalytics and tracking\nConversion rate optimization",
            ],
            5 => [
                'id' => 5,
                'title' => 'iOS & Swift - The Complete Guide',
                'instructor' => 'Paul Hudson',
                'price' => 4490,
                'originalPrice' => 17990,
                'rating' => 4.8,
                'reviews' => 5630,
                'students' => 34000,
                'hours' => 48,
                'image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=500',
                'category' => 'development',
                'badge' => 'Updated',
                'level' => 'Intermediate',
                'description' => 'Build iOS apps with SwiftUI and UIKit. Master app development for iPhone and iPad.',
                'what_you_will_learn' => "Swift programming\nSwiftUI framework\nUIKit development\nApp Store deployment\nCore Data\nPush notifications",
            ],
            6 => [
                'id' => 6,
                'title' => 'Project Management Professional (PMP)',
                'instructor' => 'Joseph Phillips',
                'price' => 3990,
                'originalPrice' => 15990,
                'rating' => 4.6,
                'reviews' => 12400,
                'students' => 78000,
                'hours' => 35,
                'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=500',
                'category' => 'business',
                'badge' => 'Certification Prep',
                'level' => 'All Levels',
                'description' => 'Prepare for PMP certification exam. Learn project management frameworks.',
                'what_you_will_learn' => "Project lifecycle\nRisk management\nStakeholder engagement\nAgile methodologies\nBudget planning\nResource allocation",
            ],
            7 => [
                'id' => 7,
                'title' => 'Python for Data Science & Machine Learning',
                'instructor' => 'Jose Portilla',
                'price' => 5490,
                'originalPrice' => 19990,
                'rating' => 4.8,
                'reviews' => 18700,
                'students' => 112000,
                'hours' => 38,
                'image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=500',
                'category' => 'data',
                'badge' => 'Best for Beginners',
                'level' => 'Beginner',
                'description' => 'Learn Python for data analysis, visualization, and machine learning.',
                'what_you_will_learn' => "Python programming\nNumPy and Pandas\nData visualization\nMachine learning\nStatistical analysis\nData cleaning",
            ],
            8 => [
                'id' => 8,
                'title' => 'The Complete Digital Marketing Course',
                'instructor' => 'Phil Ebiner',
                'price' => 2990,
                'originalPrice' => 9990,
                'rating' => 4.5,
                'reviews' => 5620,
                'students' => 48000,
                'hours' => 24,
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=500',
                'category' => 'marketing',
                'badge' => 'Hot & New',
                'level' => 'All Levels',
                'description' => 'Complete digital marketing course covering all aspects of online marketing.',
                'what_you_will_learn' => "Google Ads\nFacebook advertising\nContent marketing\nEmail campaigns\nAnalytics\nConversion tracking",
            ],
            9 => [
                'id' => 9,
                'title' => 'OpenClaw: Build & Deploy Real AI Agents',
                'instructor' => 'Alet Mely',
                'price' => 3799,
                'originalPrice' => 14999,
                'rating' => 4.7,
                'reviews' => 8930,
                'students' => 52000,
                'hours' => 42,
                'image' => 'https://images.unsplash.com/photo-1674027444485-cec3da58eefd?w=500',
                'category' => 'development',
                'badge' => 'New Release',
                'level' => 'Intermediate',
                'description' => 'Build and deploy real AI agents using OpenClaw framework.',
                'what_you_will_learn' => "AI agent architecture\nOpenClaw framework\nAutomation workflows\nAPI integration\nDeployment strategies\nMonitoring and scaling",
            ],
            10 => [
                'id' => 10,
                'title' => 'Natural Language Processing with Deep Learning',
                'instructor' => 'John Smith',
                'price' => 1299,
                'originalPrice' => 7990,
                'rating' => 4.6,
                'reviews' => 4320,
                'students' => 28000,
                'hours' => 28,
                'image' => 'https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=500',
                'category' => 'data',
                'badge' => 'Best Price',
                'level' => 'Advanced',
                'description' => 'Master NLP techniques using deep learning.',
                'what_you_will_learn' => "Tokenization and embeddings\nRNN and LSTM networks\nTransformer architecture\nBERT and GPT models\nSentiment analysis\nText generation",
            ],
            11 => [
                'id' => 11,
                'title' => 'React 19 - The Complete Guide (2026 Edition)',
                'instructor' => 'Maximilian Schwarzmüller',
                'price' => 4490,
                'originalPrice' => 16990,
                'rating' => 4.9,
                'reviews' => 15600,
                'students' => 95000,
                'hours' => 42,
                'image' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=500',
                'category' => 'development',
                'badge' => 'Trending',
                'level' => 'Intermediate',
                'description' => 'Master React 19 with hooks, context API, and server components.',
                'what_you_will_learn' => "React fundamentals\nHooks in depth\nContext API\nServer components\nState management\nPerformance optimization",
            ],
            12 => [
                'id' => 12,
                'title' => 'Cloud Computing with AWS (Solutions Architect)',
                'instructor' => 'Stephane Maarek',
                'price' => 5990,
                'originalPrice' => 24990,
                'rating' => 4.8,
                'reviews' => 21300,
                'students' => 145000,
                'hours' => 55,
                'image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=500',
                'category' => 'development',
                'badge' => 'Certification',
                'level' => 'All Levels',
                'description' => 'Prepare for AWS Solutions Architect certification.',
                'what_you_will_learn' => "EC2 and S3\nVPC networking\nLambda and serverless\nDatabase services\nSecurity and IAM\nDisaster recovery",
            ],
            13 => [
                'id' => 13,
                'title' => 'DevOps Bootcamp: Docker, Kubernetes, Jenkins',
                'instructor' => 'Mumshad Mannambeth',
                'price' => 5290,
                'originalPrice' => 19990,
                'rating' => 4.8,
                'reviews' => 12450,
                'students' => 82000,
                'hours' => 48,
                'image' => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9?w=500',
                'category' => 'development',
                'badge' => 'Popular',
                'level' => 'Intermediate',
                'description' => 'Master DevOps practices with Docker, Kubernetes, Jenkins, and CI/CD pipelines.',
                'what_you_will_learn' => "Docker containers\nKubernetes orchestration\nJenkins CI/CD\nInfrastructure as code\nMonitoring and logging\nGitOps practices",
            ],
            14 => [
                'id' => 14,
                'title' => 'Cybersecurity Fundamentals & Ethical Hacking',
                'instructor' => 'Heather Myles',
                'price' => 4990,
                'originalPrice' => 18990,
                'rating' => 4.7,
                'reviews' => 9870,
                'students' => 56000,
                'hours' => 40,
                'image' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=500',
                'category' => 'development',
                'badge' => 'Hot & New',
                'level' => 'Beginner',
                'description' => 'Learn cybersecurity fundamentals and ethical hacking techniques.',
                'what_you_will_learn' => "Network security\nPenetration testing\nVulnerability assessment\nCryptography basics\nSecurity protocols\nIncident response",
            ],
            15 => [
                'id' => 15,
                'title' => 'Flutter & Dart - Build Native Mobile Apps',
                'instructor' => 'Angela Yu',
                'price' => 4690,
                'originalPrice' => 17990,
                'rating' => 4.8,
                'reviews' => 11200,
                'students' => 73000,
                'hours' => 38,
                'image' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?w=500',
                'category' => 'development',
                'badge' => 'Updated',
                'level' => 'Beginner',
                'description' => 'Build beautiful native mobile apps for iOS and Android using Flutter and Dart.',
                'what_you_will_learn' => "Dart programming\nFlutter widgets\nState management\nAPI integration\nFirebase backend\nApp deployment",
            ],
            16 => [
                'id' => 16,
                'title' => 'TypeScript: The Complete Developer\'s Guide',
                'instructor' => 'Stephen Grider',
                'price' => 3990,
                'originalPrice' => 14990,
                'rating' => 4.9,
                'reviews' => 8430,
                'students' => 48000,
                'hours' => 32,
                'image' => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=500',
                'category' => 'development',
                'badge' => 'Top Rated',
                'level' => 'Intermediate',
                'description' => 'Master TypeScript for large-scale JavaScript applications.',
                'what_you_will_learn' => "TypeScript fundamentals\nAdvanced types\nGenerics\nDecorators\nIntegration with React\nBuild tools",
            ],
            17 => [
                'id' => 17,
                'title' => 'MBA in a Box: Business Strategy & Leadership',
                'instructor' => 'Chris Haroun',
                'price' => 6490,
                'originalPrice' => 29990,
                'rating' => 4.8,
                'reviews' => 15600,
                'students' => 89000,
                'hours' => 62,
                'image' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=500',
                'category' => 'business',
                'badge' => 'Bestseller',
                'level' => 'All Levels',
                'description' => 'Complete business education covering strategy, leadership, finance, and marketing.',
                'what_you_will_learn' => "Business strategy\nLeadership principles\nFinancial analysis\nMarketing management\nOperations\nNegotiation skills",
            ],
            18 => [
                'id' => 18,
                'title' => 'Financial Accounting & Analysis for Managers',
                'instructor' => 'Brian Bushee',
                'price' => 4490,
                'originalPrice' => 16990,
                'rating' => 4.7,
                'reviews' => 8760,
                'students' => 52000,
                'hours' => 35,
                'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=500',
                'category' => 'business',
                'badge' => 'Top Rated',
                'level' => 'Beginner',
                'description' => 'Learn financial accounting and analysis for better business decision making.',
                'what_you_will_learn' => "Financial statements\nRatio analysis\nBudgeting\nCost accounting\nInvestment decisions\nRisk assessment",
            ],
            19 => [
                'id' => 19,
                'title' => 'Data Analytics for Business Professionals',
                'instructor' => 'John Johnson',
                'price' => 3990,
                'originalPrice' => 15990,
                'rating' => 4.6,
                'reviews' => 6540,
                'students' => 41000,
                'hours' => 30,
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=500',
                'category' => 'business',
                'badge' => 'Popular',
                'level' => 'Intermediate',
                'description' => 'Master data analytics for business intelligence and strategic decision making.',
                'what_you_will_learn' => "Data visualization\nSQL for business\nExcel analytics\nBusiness intelligence\nDashboard creation\nKPI tracking",
            ],
            20 => [
                'id' => 20,
                'title' => 'Leadership & People Management Certification',
                'instructor' => 'Sheila Heen',
                'price' => 5290,
                'originalPrice' => 19990,
                'rating' => 4.8,
                'reviews' => 11200,
                'students' => 68000,
                'hours' => 28,
                'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=500',
                'category' => 'business',
                'badge' => 'Certification',
                'level' => 'Intermediate',
                'description' => 'Develop leadership skills and people management techniques for career growth.',
                'what_you_will_learn' => "Team building\nConflict resolution\nPerformance management\nCoaching skills\nEmotional intelligence\nStrategic leadership",
            ],
            21 => [
                'id' => 21,
                'title' => 'Sales & Negotiation Mastery',
                'instructor' => 'Chris Voss',
                'price' => 3790,
                'originalPrice' => 14990,
                'rating' => 4.9,
                'reviews' => 9870,
                'students' => 54000,
                'hours' => 24,
                'image' => 'https://images.unsplash.com/photo-1557426272-fc759fdf7a8d?w=500',
                'category' => 'business',
                'badge' => 'Trending',
                'level' => 'All Levels',
                'description' => 'Master the art of sales and negotiation from a former FBI negotiator.',
                'what_you_will_learn' => "Sales psychology\nNegotiation tactics\nClosing techniques\nObjection handling\nValue selling\nContract negotiation",
            ],
            22 => [
                'id' => 22,
                'title' => 'Blockchain & Cryptocurrency Fundamentals',
                'instructor' => 'George Levy',
                'price' => 4490,
                'originalPrice' => 17990,
                'rating' => 4.7,
                'reviews' => 7650,
                'students' => 47000,
                'hours' => 36,
                'image' => 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=500',
                'category' => 'development',
                'badge' => 'New',
                'level' => 'Beginner',
                'description' => 'Learn blockchain technology, cryptocurrencies, and smart contract development.',
                'what_you_will_learn' => "Blockchain basics\nCryptocurrency trading\nSmart contracts\nDeFi fundamentals\nNFT marketplace\nWeb3 development",
            ],
            23 => [
                'id' => 23,
                'title' => 'Power BI - Data Visualization & Dashboard Design',
                'instructor' => 'Maven Analytics',
                'price' => 3790,
                'originalPrice' => 14990,
                'rating' => 4.8,
                'reviews' => 8930,
                'students' => 51000,
                'hours' => 28,
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=500',
                'category' => 'data',
                'badge' => 'Best for Beginners',
                'level' => 'Beginner',
                'description' => 'Master Power BI for data visualization, dashboard design, and business intelligence.',
                'what_you_will_learn' => "Power BI desktop\nDAX formulas\nData modeling\nDashboard design\nReport publishing\nSharing and collaboration",
            ],
            24 => [
                'id' => 24,
                'title' => 'UX Research & Strategy: Design with Data',
                'instructor' => 'Megan Smith',
                'price' => 4290,
                'originalPrice' => 16990,
                'rating' => 4.8,
                'reviews' => 5430,
                'students' => 34000,
                'hours' => 34,
                'image' => 'https://images.unsplash.com/photo-1586717791821-3f44a563fa4c?w=500',
                'category' => 'design',
                'badge' => 'Popular',
                'level' => 'Intermediate',
                'description' => 'Learn UX research methods and strategy to create data-driven designs.',
                'what_you_will_learn' => "User research\nUsability testing\nInformation architecture\nPersona creation\nJourney mapping\nResearch analysis",
            ],
            25 => [
                'id' => 25,
                'title' => 'SEO & Content Marketing Masterclass',
                'instructor' => 'Brian Dean',
                'price' => 3490,
                'originalPrice' => 12990,
                'rating' => 4.7,
                'reviews' => 12300,
                'students' => 76000,
                'hours' => 26,
                'image' => 'https://images.unsplash.com/photo-1432888622747-4eb9a8f2c293?w=500',
                'category' => 'marketing',
                'badge' => 'Trending',
                'level' => 'All Levels',
                'description' => 'Master SEO and content marketing strategies to drive organic traffic.',
                'what_you_will_learn' => "Keyword research\nOn-page SEO\nLink building\nContent strategy\nSEO analytics\nLocal SEO",
            ],
            26 => [
                'id' => 26,
                'title' => 'Product Management: From Idea to Launch',
                'instructor' => 'Marty Cagan',
                'price' => 4990,
                'originalPrice' => 18990,
                'rating' => 4.8,
                'reviews' => 9870,
                'students' => 59000,
                'hours' => 40,
                'image' => 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=500',
                'category' => 'business',
                'badge' => 'Top Rated',
                'level' => 'Intermediate',
                'description' => 'Learn product management from ideation to launch. Master agile and product strategy.',
                'what_you_will_learn' => "Product strategy\nUser story mapping\nAgile methodology\nMVP development\nProduct metrics\nGo-to-market strategy",
            ],
        ];
        
        $courseData = $courses[(int)$id] ?? $courses[1];
        
        // Add unique curriculum and reviews
        $courseData['uniqueCurriculum'] = $this->getCurriculumForCourse($id);
        $courseData['uniqueReviews'] = $this->getReviewsForCourse($id);
        
        return $courseData;
    }

    private function getCurriculumForCourse($courseId)
    {
        $curriculums = [
            1 => [ // Web Development
                ['id' => 1, 'title' => 'HTML & CSS Fundamentals', 'lessons' => [
                    ['id' => 1, 'title' => 'HTML Structure', 'duration' => '25 min'],
                    ['id' => 2, 'title' => 'CSS Selectors', 'duration' => '30 min'],
                    ['id' => 3, 'title' => 'Flexbox & Grid', 'duration' => '35 min'],
                    ['id' => 4, 'title' => 'Responsive Design', 'duration' => '28 min'],
                ]],
                ['id' => 2, 'title' => 'JavaScript Mastery', 'lessons' => [
                    ['id' => 5, 'title' => 'ES6+ Features', 'duration' => '40 min'],
                    ['id' => 6, 'title' => 'DOM Manipulation', 'duration' => '35 min'],
                    ['id' => 7, 'title' => 'Async JavaScript', 'duration' => '45 min'],
                    ['id' => 8, 'title' => 'API Integration', 'duration' => '38 min'],
                ]],
                ['id' => 3, 'title' => 'React.js', 'lessons' => [
                    ['id' => 9, 'title' => 'Components & Props', 'duration' => '30 min'],
                    ['id' => 10, 'title' => 'State & Hooks', 'duration' => '50 min'],
                    ['id' => 11, 'title' => 'Context API', 'duration' => '35 min'],
                ]],
                ['id' => 4, 'title' => 'Node.js Backend', 'lessons' => [
                    ['id' => 12, 'title' => 'Express Framework', 'duration' => '40 min'],
                    ['id' => 13, 'title' => 'Database Integration', 'duration' => '45 min'],
                    ['id' => 14, 'title' => 'Authentication', 'duration' => '50 min'],
                ]],
            ],
            2 => [ // UI/UX Design
                ['id' => 1, 'title' => 'Design Fundamentals', 'lessons' => [
                    ['id' => 1, 'title' => 'Color Theory', 'duration' => '20 min'],
                    ['id' => 2, 'title' => 'Typography', 'duration' => '25 min'],
                    ['id' => 3, 'title' => 'Visual Hierarchy', 'duration' => '22 min'],
                ]],
                ['id' => 2, 'title' => 'Figma Mastery', 'lessons' => [
                    ['id' => 4, 'title' => 'Auto Layout', 'duration' => '30 min'],
                    ['id' => 5, 'title' => 'Components', 'duration' => '35 min'],
                    ['id' => 6, 'title' => 'Prototyping', 'duration' => '28 min'],
                ]],
                ['id' => 3, 'title' => 'User Research', 'lessons' => [
                    ['id' => 7, 'title' => 'User Interviews', 'duration' => '25 min'],
                    ['id' => 8, 'title' => 'Persona Creation', 'duration' => '20 min'],
                    ['id' => 9, 'title' => 'Journey Mapping', 'duration' => '30 min'],
                ]],
            ],
            3 => [ // AI & Machine Learning
                ['id' => 1, 'title' => 'ML Basics', 'lessons' => [
                    ['id' => 1, 'title' => 'Supervised Learning', 'duration' => '25 min'],
                    ['id' => 2, 'title' => 'Regression Models', 'duration' => '35 min'],
                    ['id' => 3, 'title' => 'Classification', 'duration' => '30 min'],
                ]],
                ['id' => 2, 'title' => 'Neural Networks', 'lessons' => [
                    ['id' => 4, 'title' => 'Perceptrons', 'duration' => '20 min'],
                    ['id' => 5, 'title' => 'Backpropagation', 'duration' => '40 min'],
                    ['id' => 6, 'title' => 'CNN Architecture', 'duration' => '35 min'],
                ]],
                ['id' => 3, 'title' => 'Deep Learning', 'lessons' => [
                    ['id' => 7, 'title' => 'TensorFlow', 'duration' => '45 min'],
                    ['id' => 8, 'title' => 'PyTorch', 'duration' => '40 min'],
                    ['id' => 9, 'title' => 'Model Deployment', 'duration' => '35 min'],
                ]],
            ],
            4 => [ // Digital Marketing
                ['id' => 1, 'title' => 'Social Media', 'lessons' => [
                    ['id' => 1, 'title' => 'Instagram Strategy', 'duration' => '30 min'],
                    ['id' => 2, 'title' => 'TikTok Growth', 'duration' => '25 min'],
                    ['id' => 3, 'title' => 'LinkedIn Lead Gen', 'duration' => '28 min'],
                ]],
                ['id' => 2, 'title' => 'SEO Mastery', 'lessons' => [
                    ['id' => 4, 'title' => 'Keyword Research', 'duration' => '35 min'],
                    ['id' => 5, 'title' => 'On-Page SEO', 'duration' => '30 min'],
                    ['id' => 6, 'title' => 'Link Building', 'duration' => '40 min'],
                ]],
                ['id' => 3, 'title' => 'Email Marketing', 'lessons' => [
                    ['id' => 7, 'title' => 'Campaign Strategy', 'duration' => '25 min'],
                    ['id' => 8, 'title' => 'Automation', 'duration' => '30 min'],
                ]],
            ],
            5 => [ // iOS & Swift
                ['id' => 1, 'title' => 'Swift Basics', 'lessons' => [
                    ['id' => 1, 'title' => 'Variables & Types', 'duration' => '20 min'],
                    ['id' => 2, 'title' => 'Control Flow', 'duration' => '25 min'],
                    ['id' => 3, 'title' => 'Functions', 'duration' => '22 min'],
                ]],
                ['id' => 2, 'title' => 'SwiftUI', 'lessons' => [
                    ['id' => 4, 'title' => 'Views & Modifiers', 'duration' => '35 min'],
                    ['id' => 5, 'title' => 'State Management', 'duration' => '40 min'],
                    ['id' => 6, 'title' => 'Navigation', 'duration' => '30 min'],
                ]],
                ['id' => 3, 'title' => 'App Development', 'lessons' => [
                    ['id' => 7, 'title' => 'API Integration', 'duration' => '45 min'],
                    ['id' => 8, 'title' => 'Core Data', 'duration' => '50 min'],
                ]],
            ],
            6 => [ // PMP Certification
                ['id' => 1, 'title' => 'Project Initiation', 'lessons' => [
                    ['id' => 1, 'title' => 'Project Charter', 'duration' => '25 min'],
                    ['id' => 2, 'title' => 'Stakeholder Analysis', 'duration' => '30 min'],
                ]],
                ['id' => 2, 'title' => 'Project Planning', 'lessons' => [
                    ['id' => 3, 'title' => 'WBS', 'duration' => '35 min'],
                    ['id' => 4, 'title' => 'Schedule Management', 'duration' => '40 min'],
                ]],
                ['id' => 3, 'title' => 'Risk Management', 'lessons' => [
                    ['id' => 5, 'title' => 'Risk Assessment', 'duration' => '30 min'],
                    ['id' => 6, 'title' => 'Mitigation Strategies', 'duration' => '35 min'],
                ]],
            ],
            7 => [ // Python Data Science
                ['id' => 1, 'title' => 'Python Basics', 'lessons' => [
                    ['id' => 1, 'title' => 'Variables & Loops', 'duration' => '25 min'],
                    ['id' => 2, 'title' => 'Functions', 'duration' => '20 min'],
                ]],
                ['id' => 2, 'title' => 'Pandas & NumPy', 'lessons' => [
                    ['id' => 3, 'title' => 'DataFrames', 'duration' => '35 min'],
                    ['id' => 4, 'title' => 'Data Cleaning', 'duration' => '30 min'],
                ]],
                ['id' => 3, 'title' => 'Visualization', 'lessons' => [
                    ['id' => 5, 'title' => 'Matplotlib', 'duration' => '28 min'],
                    ['id' => 6, 'title' => 'Seaborn', 'duration' => '25 min'],
                ]],
            ],
            8 => [ // Digital Marketing Complete
                ['id' => 1, 'title' => 'Google Ads', 'lessons' => [
                    ['id' => 1, 'title' => 'Campaign Setup', 'duration' => '30 min'],
                    ['id' => 2, 'title' => 'Keyword Targeting', 'duration' => '25 min'],
                ]],
                ['id' => 2, 'title' => 'Facebook Ads', 'lessons' => [
                    ['id' => 3, 'title' => 'Audience Targeting', 'duration' => '35 min'],
                    ['id' => 4, 'title' => 'Ad Creation', 'duration' => '30 min'],
                ]],
                ['id' => 3, 'title' => 'Analytics', 'lessons' => [
                    ['id' => 5, 'title' => 'Google Analytics', 'duration' => '28 min'],
                    ['id' => 6, 'title' => 'Conversion Tracking', 'duration' => '25 min'],
                ]],
            ],
            9 => [ // AI Agents
                ['id' => 1, 'title' => 'AI Agent Basics', 'lessons' => [
                    ['id' => 1, 'title' => 'Agent Architecture', 'duration' => '30 min'],
                    ['id' => 2, 'title' => 'OpenClaw Setup', 'duration' => '25 min'],
                ]],
                ['id' => 2, 'title' => 'Building Agents', 'lessons' => [
                    ['id' => 3, 'title' => 'Workflow Automation', 'duration' => '40 min'],
                    ['id' => 4, 'title' => 'API Integration', 'duration' => '35 min'],
                ]],
                ['id' => 3, 'title' => 'Deployment', 'lessons' => [
                    ['id' => 5, 'title' => 'Cloud Deployment', 'duration' => '30 min'],
                    ['id' => 6, 'title' => 'Monitoring', 'duration' => '25 min'],
                ]],
            ],
            10 => [ // NLP
                ['id' => 1, 'title' => 'Text Processing', 'lessons' => [
                    ['id' => 1, 'title' => 'Tokenization', 'duration' => '20 min'],
                    ['id' => 2, 'title' => 'Embeddings', 'duration' => '30 min'],
                ]],
                ['id' => 2, 'title' => 'Transformers', 'lessons' => [
                    ['id' => 3, 'title' => 'Attention Mechanism', 'duration' => '35 min'],
                    ['id' => 4, 'title' => 'BERT Model', 'duration' => '40 min'],
                ]],
                ['id' => 3, 'title' => 'Applications', 'lessons' => [
                    ['id' => 5, 'title' => 'Sentiment Analysis', 'duration' => '25 min'],
                    ['id' => 6, 'title' => 'Text Generation', 'duration' => '30 min'],
                ]],
            ],
            11 => [ // React 19
                ['id' => 1, 'title' => 'React Basics', 'lessons' => [
                    ['id' => 1, 'title' => 'Components', 'duration' => '25 min'],
                    ['id' => 2, 'title' => 'JSX', 'duration' => '20 min'],
                ]],
                ['id' => 2, 'title' => 'Hooks', 'lessons' => [
                    ['id' => 3, 'title' => 'useState', 'duration' => '30 min'],
                    ['id' => 4, 'title' => 'useEffect', 'duration' => '35 min'],
                    ['id' => 5, 'title' => 'Custom Hooks', 'duration' => '28 min'],
                ]],
                ['id' => 3, 'title' => 'Advanced', 'lessons' => [
                    ['id' => 6, 'title' => 'Context API', 'duration' => '30 min'],
                    ['id' => 7, 'title' => 'Server Components', 'duration' => '35 min'],
                ]],
            ],
            12 => [ // AWS Cloud
                ['id' => 1, 'title' => 'EC2 & S3', 'lessons' => [
                    ['id' => 1, 'title' => 'EC2 Setup', 'duration' => '35 min'],
                    ['id' => 2, 'title' => 'S3 Buckets', 'duration' => '30 min'],
                ]],
                ['id' => 2, 'title' => 'Networking', 'lessons' => [
                    ['id' => 3, 'title' => 'VPC', 'duration' => '40 min'],
                    ['id' => 4, 'title' => 'Security Groups', 'duration' => '25 min'],
                ]],
                ['id' => 3, 'title' => 'Serverless', 'lessons' => [
                    ['id' => 5, 'title' => 'Lambda Functions', 'duration' => '35 min'],
                    ['id' => 6, 'title' => 'API Gateway', 'duration' => '30 min'],
                ]],
            ],
            13 => [ // DevOps
                ['id' => 1, 'title' => 'Docker', 'lessons' => [
                    ['id' => 1, 'title' => 'Containers', 'duration' => '30 min'],
                    ['id' => 2, 'title' => 'Docker Compose', 'duration' => '35 min'],
                ]],
                ['id' => 2, 'title' => 'Kubernetes', 'lessons' => [
                    ['id' => 3, 'title' => 'Pods & Services', 'duration' => '40 min'],
                    ['id' => 4, 'title' => 'Deployments', 'duration' => '35 min'],
                ]],
                ['id' => 3, 'title' => 'CI/CD', 'lessons' => [
                    ['id' => 5, 'title' => 'Jenkins Pipeline', 'duration' => '30 min'],
                    ['id' => 6, 'title' => 'GitHub Actions', 'duration' => '28 min'],
                ]],
            ],
            14 => [ // Cybersecurity
                ['id' => 1, 'title' => 'Network Security', 'lessons' => [
                    ['id' => 1, 'title' => 'Firewalls', 'duration' => '25 min'],
                    ['id' => 2, 'title' => 'VPN', 'duration' => '30 min'],
                ]],
                ['id' => 2, 'title' => 'Ethical Hacking', 'lessons' => [
                    ['id' => 3, 'title' => 'Penetration Testing', 'duration' => '40 min'],
                    ['id' => 4, 'title' => 'Vulnerability Scanning', 'duration' => '35 min'],
                ]],
                ['id' => 3, 'title' => 'Security Best Practices', 'lessons' => [
                    ['id' => 5, 'title' => 'Encryption', 'duration' => '25 min'],
                    ['id' => 6, 'title' => 'Incident Response', 'duration' => '30 min'],
                ]],
            ],
            15 => [ // Flutter
                ['id' => 1, 'title' => 'Dart Basics', 'lessons' => [
                    ['id' => 1, 'title' => 'Variables & Functions', 'duration' => '25 min'],
                    ['id' => 2, 'title' => 'Classes', 'duration' => '20 min'],
                ]],
                ['id' => 2, 'title' => 'Flutter Widgets', 'lessons' => [
                    ['id' => 3, 'title' => 'Stateless Widgets', 'duration' => '30 min'],
                    ['id' => 4, 'title' => 'Stateful Widgets', 'duration' => '35 min'],
                ]],
                ['id' => 3, 'title' => 'App Development', 'lessons' => [
                    ['id' => 5, 'title' => 'Navigation', 'duration' => '28 min'],
                    ['id' => 6, 'title' => 'API Integration', 'duration' => '35 min'],
                ]],
            ],
            16 => [ // TypeScript
                ['id' => 1, 'title' => 'TypeScript Basics', 'lessons' => [
                    ['id' => 1, 'title' => 'Types & Interfaces', 'duration' => '25 min'],
                    ['id' => 2, 'title' => 'Functions', 'duration' => '20 min'],
                ]],
                ['id' => 2, 'title' => 'Advanced Types', 'lessons' => [
                    ['id' => 3, 'title' => 'Generics', 'duration' => '35 min'],
                    ['id' => 4, 'title' => 'Decorators', 'duration' => '30 min'],
                ]],
                ['id' => 3, 'title' => 'React with TS', 'lessons' => [
                    ['id' => 5, 'title' => 'Props & State', 'duration' => '28 min'],
                    ['id' => 6, 'title' => 'Hooks with TS', 'duration' => '32 min'],
                ]],
            ],
            17 => [ // MBA - Business Strategy
                ['id' => 1, 'title' => 'Business Strategy', 'lessons' => [
                    ['id' => 1, 'title' => 'SWOT Analysis', 'duration' => '30 min'],
                    ['id' => 2, 'title' => 'Porter\'s Five Forces', 'duration' => '35 min'],
                ]],
                ['id' => 2, 'title' => 'Leadership', 'lessons' => [
                    ['id' => 3, 'title' => 'Leadership Styles', 'duration' => '28 min'],
                    ['id' => 4, 'title' => 'Team Management', 'duration' => '32 min'],
                ]],
                ['id' => 3, 'title' => 'Finance', 'lessons' => [
                    ['id' => 5, 'title' => 'Financial Statements', 'duration' => '40 min'],
                    ['id' => 6, 'title' => 'Valuation', 'duration' => '45 min'],
                ]],
            ],
        ];
        
        if (isset($curriculums[$courseId])) {
            return $curriculums[$courseId];
        }
        
        // Default curriculum for courses 18-26
        return [
            ['id' => 1, 'title' => 'Introduction', 'lessons' => [
                ['id' => 1, 'title' => 'Course Overview', 'duration' => '15 min'],
                ['id' => 2, 'title' => 'Getting Started', 'duration' => '20 min'],
            ]],
            ['id' => 2, 'title' => 'Core Concepts', 'lessons' => [
                ['id' => 3, 'title' => 'Fundamentals', 'duration' => '30 min'],
                ['id' => 4, 'title' => 'Advanced Topics', 'duration' => '40 min'],
            ]],
            ['id' => 3, 'title' => 'Practical Projects', 'lessons' => [
                ['id' => 5, 'title' => 'Real-World Applications', 'duration' => '45 min'],
                ['id' => 6, 'title' => 'Case Studies', 'duration' => '35 min'],
            ]],
        ];
    }

    private function getReviewsForCourse($courseId)
    {
        // Unique reviews for ALL 26 courses
        $allReviews = [
            1 => [ // Web Development
                ['id' => 1, 'user' => ['name' => 'Sarah Johnson', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-15', 'comment' => 'Absolutely amazing web dev course! Built my portfolio and got a job!', 'helpful' => 128],
                ['id' => 2, 'user' => ['name' => 'Whiskers', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-10', 'comment' => 'Meow! This web course is purr-fect! My human is now a developer! 🐱', 'helpful' => 42],
                ['id' => 3, 'user' => ['name' => 'Michael Chen', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-03-05', 'comment' => 'Landed a frontend job after this course! Best investment ever.', 'helpful' => 95],
            ],
            2 => [ // UI/UX Design
                ['id' => 1, 'user' => ['name' => 'Jessica Wong', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-12', 'comment' => 'Her Figma tutorials are top-notch! My designs look professional now!', 'helpful' => 56],
                ['id' => 2, 'user' => ['name' => 'Oliver', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-08', 'comment' => 'My human is now a UX designer! Purr-fect! 🐱', 'helpful' => 28],
                ['id' => 3, 'user' => ['name' => 'Marcus Thompson', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 4, 'date' => '2026-03-01', 'comment' => 'Learned so much about design systems.', 'helpful' => 43],
            ],
            3 => [ // AI & Machine Learning
                ['id' => 1, 'user' => ['name' => 'Dr. Lisa Park', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-14', 'comment' => 'Andrew Ng explains complex AI concepts so clearly! This course is gold!', 'helpful' => 203],
                ['id' => 2, 'user' => ['name' => 'Tom', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-09', 'comment' => 'Even a cat can understand neural networks now! 🐱🤖', 'helpful' => 67],
                ['id' => 3, 'user' => ['name' => 'Alex Rivera', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-03-02', 'comment' => 'The deep learning section is incredible.', 'helpful' => 89],
            ],
            4 => [ // Digital Marketing & Growth Hacking
                ['id' => 1, 'user' => ['name' => 'Nina Patel', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-11', 'comment' => 'Gary Vee knows his stuff! My engagement increased 300%!', 'helpful' => 78],
                ['id' => 2, 'user' => ['name' => 'Luna', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 4, 'date' => '2026-03-07', 'comment' => 'My human is a marketing expert now! 🐱📈', 'helpful' => 31],
                ['id' => 3, 'user' => ['name' => 'David Kim', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-02-28', 'comment' => 'SEO section alone is worth the price!', 'helpful' => 52],
            ],
            5 => [ // iOS & Swift
                ['id' => 1, 'user' => ['name' => 'Rachel Green', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-13', 'comment' => 'Finally understand iOS development! Paul Hudson is the best!', 'helpful' => 67],
                ['id' => 2, 'user' => ['name' => 'Simba', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-06', 'comment' => 'My human built an iPhone app! Amazing! 🐱📱', 'helpful' => 23],
                ['id' => 3, 'user' => ['name' => 'Chris Evans', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 4, 'date' => '2026-02-25', 'comment' => 'SwiftUI section is fantastic!', 'helpful' => 44],
            ],
            6 => [ // PMP Certification
                ['id' => 1, 'user' => ['name' => 'Maria Garcia', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-10', 'comment' => 'Passed my PMP exam on first try! This course prepared me perfectly.', 'helpful' => 112],
                ['id' => 2, 'user' => ['name' => 'Felix', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-05', 'comment' => 'My human is now a certified project manager! Meow! 🐱', 'helpful' => 34],
                ['id' => 3, 'user' => ['name' => 'James Wilson', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 4, 'date' => '2026-02-28', 'comment' => 'Very comprehensive PMP prep course.', 'helpful' => 56],
            ],
            7 => [ // Python Data Science
                ['id' => 1, 'user' => ['name' => 'Priya Sharma', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-12', 'comment' => 'Finally understand pandas and numpy! Great practical examples.', 'helpful' => 89],
                ['id' => 2, 'user' => ['name' => 'Milo', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-08', 'comment' => 'My human is a data scientist now! 🐱📊', 'helpful' => 27],
                ['id' => 3, 'user' => ['name' => 'Kevin Lee', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-03-01', 'comment' => 'The visualization section is amazing!', 'helpful' => 63],
            ],
            8 => [ // Complete Digital Marketing
                ['id' => 1, 'user' => ['name' => 'Amanda Brown', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-09', 'comment' => 'Covered everything from Google Ads to email marketing!', 'helpful' => 71],
                ['id' => 2, 'user' => ['name' => 'Charlie', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 4, 'date' => '2026-03-04', 'comment' => 'My human is now a digital marketing expert! 🐱💻', 'helpful' => 19],
                ['id' => 3, 'user' => ['name' => 'Thomas Wright', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-02-27', 'comment' => 'Worth every penny!', 'helpful' => 48],
            ],
            9 => [ // OpenClaw AI Agents
                ['id' => 1, 'user' => ['name' => 'Sophia Chen', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-11', 'comment' => 'Built my first AI agent! The OpenClaw framework is powerful.', 'helpful' => 94],
                ['id' => 2, 'user' => ['name' => 'Loki', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-06', 'comment' => 'Even I can build AI agents now! Meow! 🐱🤖', 'helpful' => 41],
                ['id' => 3, 'user' => ['name' => 'Daniel Park', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 4, 'date' => '2026-02-28', 'comment' => 'Great introduction to AI agents.', 'helpful' => 52],
            ],
            10 => [ // NLP
                ['id' => 1, 'user' => ['name' => 'Emma Watson', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-10', 'comment' => 'The transformer explanation is brilliant! Finally understand BERT.', 'helpful' => 156],
                ['id' => 2, 'user' => ['name' => 'Oscar', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-05', 'comment' => 'My human can now talk to computers! 🐱💬', 'helpful' => 38],
                ['id' => 3, 'user' => ['name' => 'Nathan Kim', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-02-28', 'comment' => 'The attention mechanism explanation was eye-opening!', 'helpful' => 73],
            ],
            11 => [ // React 19
                ['id' => 1, 'user' => ['name' => 'Taylor Swift', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-09', 'comment' => 'The hooks explanation is the best I\'ve ever seen! Finally understand useEffect!', 'helpful' => 134],
                ['id' => 2, 'user' => ['name' => 'Mochi', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-04', 'comment' => 'My human builds amazing React apps now! Meow! 🐱⚛️', 'helpful' => 45],
                ['id' => 3, 'user' => ['name' => 'Ryan Gosling', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-02-26', 'comment' => 'Server Components are a game-changer! Highly recommend.', 'helpful' => 89],
            ],
            12 => [ // AWS Cloud
                ['id' => 1, 'user' => ['name' => 'Michelle Obama', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-08', 'comment' => 'Passed my Solutions Architect exam! This course is comprehensive.', 'helpful' => 187],
                ['id' => 2, 'user' => ['name' => 'Ginger', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-03', 'comment' => 'My human is now cloud certified! Purr-fect course! 🐱☁️', 'helpful' => 32],
                ['id' => 3, 'user' => ['name' => 'Elon Musk', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-02-25', 'comment' => 'Stephane is the best AWS instructor. Period.', 'helpful' => 245],
            ],
            13 => [ // DevOps
                ['id' => 1, 'user' => ['name' => 'Ada Lovelace', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-07', 'comment' => 'Docker and Kubernetes finally make sense! Great practical labs.', 'helpful' => 98],
                ['id' => 2, 'user' => ['name' => 'Socks', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-02', 'comment' => 'My human is a DevOps engineer now! 🐱🐳', 'helpful' => 29],
                ['id' => 3, 'user' => ['name' => 'Linus Torvalds', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-02-24', 'comment' => 'The CI/CD pipeline section is outstanding.', 'helpful' => 112],
            ],
            14 => [ // Cybersecurity
                ['id' => 1, 'user' => ['name' => 'Grace Hopper', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-06', 'comment' => 'Learned real ethical hacking techniques. The penetration testing lab is fantastic!', 'helpful' => 156],
                ['id' => 2, 'user' => ['name' => 'Shadow', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-03-01', 'comment' => 'My human can now protect systems from hackers! 🐱🔒', 'helpful' => 41],
                ['id' => 3, 'user' => ['name' => 'Kevin Mitnick', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-02-23', 'comment' => 'As a former hacker, I approve this course!', 'helpful' => 203],
            ],
            15 => [ // Flutter
                ['id' => 1, 'user' => ['name' => 'Zoe Deschanel', 'avatar' => '👩', 'avatarType' => 'female'], 'rating' => 5, 'date' => '2026-03-05', 'comment' => 'Built two apps already! Flutter is amazing and Angela teaches so well.', 'helpful' => 87],
                ['id' => 2, 'user' => ['name' => 'Nala', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-02-28', 'comment' => 'My human builds apps for both iOS AND Android now! 🐱📱', 'helpful' => 34],
                ['id' => 3, 'user' => ['name' => 'Tim Cook', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 4, 'date' => '2026-02-22', 'comment' => 'Great cross-platform development course.', 'helpful' => 67],
            ],
            16 => [ // TypeScript
                ['id' => 1, 'user' => ['name' => 'Sundar Pichai', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-03-04', 'comment' => 'TypeScript is a game-changer for large projects. Stephen explains it perfectly.', 'helpful' => 123],
                ['id' => 2, 'user' => ['name' => 'Lily', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 5, 'date' => '2026-02-27', 'comment' => 'My human writes type-safe code now! No more bugs! 🐱✨', 'helpful' => 28],
                ['id' => 3, 'user' => ['name' => 'Mark Zuckerberg', 'avatar' => '👨', 'avatarType' => 'male'], 'rating' => 5, 'date' => '2026-02-21', 'comment' => 'The generics section alone is worth the price.', 'helpful' => 91],
            ],
        ];
        
        // Add reviews for courses 17-26 dynamically
        for ($i = 17; $i <= 26; $i++) {
            if (!isset($allReviews[$i])) {
                $allReviews[$i] = [
                    ['id' => 1, 'user' => ['name' => $i % 2 == 0 ? 'Jennifer Lawrence' : 'Brad Pitt', 'avatar' => $i % 2 == 0 ? '👩' : '👨', 'avatarType' => $i % 2 == 0 ? 'female' : 'male'], 'rating' => 5, 'date' => '2026-03-' . rand(10, 20), 'comment' => 'This course exceeded my expectations! Very well structured and informative.', 'helpful' => rand(50, 150)],
                    ['id' => 2, 'user' => ['name' => $i % 3 == 0 ? 'Mittens' : 'Fluffy', 'avatar' => '🐱', 'avatarType' => 'cat'], 'rating' => 4, 'date' => '2026-03-' . rand(5, 15), 'comment' => 'Meow! My human loves learning from this course. Highly recommended! 🐱', 'helpful' => rand(20, 60)],
                    ['id' => 3, 'user' => ['name' => $i % 2 == 0 ? 'Chris Hemsworth' : 'Scarlett Johansson', 'avatar' => $i % 2 == 0 ? '👨' : '👩', 'avatarType' => $i % 2 == 0 ? 'male' : 'female'], 'rating' => 5, 'date' => '2026-02-' . rand(20, 28), 'comment' => 'Excellent content! The instructor is very knowledgeable and engaging.', 'helpful' => rand(30, 100)],
                ];
            }
        }
        
        return $allReviews[$courseId] ?? $allReviews[1];
    }
    
    public function learn($slug)
    {
        $course = Course::with([
            'sections' => function($q) {
                $q->orderBy('order_position');
            },
            'sections.lessons' => function($q) {
                $q->orderBy('order');
            },
            'sections.quizzes',
        ])->where('slug', $slug)->firstOrFail();
        
        $enrollment = Enrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->firstOrFail();
        
        // Get first lesson or first quiz
        $firstLesson = null;
        $firstQuiz = null;
        
        foreach ($course->sections as $section) {
            if ($section->lessons->isNotEmpty()) {
                $firstLesson = $section->lessons->first();
                break;
            }
            if ($section->quizzes->isNotEmpty() && !$firstQuiz) {
                $firstQuiz = $section->quizzes->first();
            }
        }
        
        // Get completed lesson IDs
        $completedLessons = ProgressTracking::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();
        
        // Get passed quizzes
        $passedQuizzes = QuizAttempt::where('user_id', Auth::id())
            ->where('is_passed', true)
            ->pluck('quiz_id')
            ->toArray();
        
        // Calculate progress
        $totalLessons = $course->sections->sum(function($s) { return $s->lessons->count(); });
        $totalQuizzes = $course->sections->sum(function($s) { return $s->quizzes->count(); });
        $totalItems = $totalLessons + $totalQuizzes;
        
        $completedItems = count($completedLessons) + count($passedQuizzes);
        $progressPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
        
        // Update enrollment progress
        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'last_accessed_at' => now(),
            'status' => $progressPercentage >= 100 ? 'completed' : 'active',
            'completed_at' => $progressPercentage >= 100 ? now() : $enrollment->completed_at,
        ]);
        
        // Auto-generate certificate if completed
        if ($progressPercentage >= 100 && !$enrollment->certificate_issued) {
            $this->generateCertificate($enrollment);
        }
        
        return Inertia::render('Course/Learn', [
            'course' => $course,
            'enrollment' => $enrollment,
            'completedLessons' => $completedLessons,
            'passedQuizzes' => $passedQuizzes,
            'firstLesson' => $firstLesson,
            'firstQuiz' => $firstQuiz,
            'progressPercentage' => $progressPercentage,
        ]);
    }
    
    private function generateCertificate($enrollment)
    {
        $existingCertificate = Certificate::where('user_id', $enrollment->user_id)
            ->where('course_id', $enrollment->course_id)
            ->first();
        
        if (!$existingCertificate) {
            $certificateNumber = 'CERT-' . strtoupper(uniqid()) . '-' . $enrollment->id;
            
            $certificate = Certificate::create([
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'enrollment_id' => $enrollment->id,
                'certificate_number' => $certificateNumber,
                'issued_at' => now(),
            ]);
            
            $enrollment->update(['certificate_issued' => true]);
            
            // Notify user
            Notification::create([
                'user_id' => $enrollment->user_id,
                'type' => 'certificate_issued',
                'title' => '🎓 Certificate Earned!',
                'message' => 'Congratulations! You have completed the course and earned your certificate.',
                'action_url' => route('certificate.show', $certificate->id),
            ]);
        }
    }
    public function featured()
{
    // Fetch courses (for example, those with a 'featured' flag or just the latest)
    $courses = Course::where('is_featured', true)->get(); 
    
    return response()->json($courses);
}
}