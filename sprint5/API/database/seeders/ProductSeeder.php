<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Category-based CO2 rating logic
        // B = Low impact (recycled materials, sustainable wood)
        // D = Higher impact (steel tools)
        // E = High impact (power tools)
        $categoryRatings = [
            'Hand Saw' => 'B',           // Wood handles, traditional tools
            'Safety Gear' => 'B',        // Often recycled materials/fabrics
            'Storage Solutions' => 'B',  // Recycled plastics
            'Tool Belts' => 'B',         // Fabric/recycled materials
            'Workbench' => 'B',          // Wood construction
            'Measures' => 'C',           // Mixed materials
            'Fasteners' => 'C',          // Small metal parts
            'Screwdriver' => 'C',        // Mixed materials
            'Pliers' => 'D',             // Steel construction
            'Hammer' => 'D',             // Steel construction
            'Wrench' => 'D',             // Steel construction
            'Chisels' => 'D',            // Steel construction
            'Saw' => 'D',                // Metal saw blades
            'Drill' => 'E',              // Power tools
            'Sander' => 'E',             // Power tools
        ];

        DB::table('products')->insert([[
            'id' => Str::ulid()->toBase32(),
            'name' => 'Combination Pliers',
            'description' => 'Versatile combination pliers designed for gripping, bending, and cutting wire with ease. Featuring chrome vanadium steel construction with induction-hardened cutting edges, these pliers deliver excellent grip and leverage for a wide range of tasks. The precision-machined jaws combine flat gripping surfaces with a pipe-grip section and integrated wire cutter for true multi-purpose functionality. Ergonomic bi-component handles reduce hand fatigue during extended use and provide a secure hold even with oily or gloved hands. The joint is precisely fitted to eliminate play and ensure smooth operation over thousands of cycles. Ideal for electricians, mechanics, and DIY enthusiasts tackling everyday projects around the workshop or job site.',
            'stock' => 25,
            'price' => 14.15,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'A'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Pliers',
            'description' => 'Reliable general-purpose pliers crafted from drop-forged carbon steel for long-lasting durability in demanding working conditions. The serrated jaws provide a firm grip on a variety of materials including wire, nails, small fasteners, and irregularly shaped objects. Comfortable rubber-coated handles absorb vibration and reduce hand fatigue during extended use, while the non-slip surface ensures confident handling. The precision pivot joint maintains alignment under load, delivering smooth, controlled jaw action with every squeeze. Heat-treated steel construction resists wear and deformation even under heavy use. A must-have foundational tool for any workshop, equally suited for professional tradespeople and home repair projects.',
            'stock' => 25,
            'price' => 12.01,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Bolt Cutters',
            'description' => 'Heavy-duty bolt cutters engineered to slice through hardened bolts, chains, padlocks, and wire fencing with minimal effort. The compound leverage mechanism multiplies your cutting force by a factor of 30, enabling clean cuts through materials up to 10mm in diameter. Hardened alloy steel blades are precisely ground and heat-treated to maintain a sharp edge through years of repeated use on tough materials. Ergonomic rubber grips and the generous 750mm handle length provide excellent control, leverage, and reach during overhead or awkward-angle cuts. An adjustable blade tension screw lets you fine-tune the jaw alignment for optimal cutting performance. Built for demolition crews, locksmiths, fencing contractors, and maintenance professionals who need reliable cutting power day after day.',
            'stock' => 25,
            'price' => 48.41,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers03.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Long Nose Pliers',
            'description' => 'Precision long nose pliers with narrow tapered jaws designed for reaching into tight, confined spaces and bending fine wire with accuracy. Made from chrome vanadium steel with induction-hardened cutting edges that cleanly sever copper, brass, and steel wire without crushing or fraying. The slim jaw profile tapers to a fine point, making these pliers indispensable for electrical terminal work, jewelry making, and intricate mechanical repairs. Bi-component comfort grips minimize hand strain during detailed tasks requiring a steady hand and controlled pressure. The leaf-spring joint returns the handles to the open position automatically, speeding up repetitive tasks. A must-have precision tool trusted by electricians, electronics technicians, and hobbyists worldwide.',
            'stock' => 0,
            'price' => 14.24,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers04.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Slip Joint Pliers',
            'description' => 'Adjustable slip joint pliers that feature a two-position pivot mechanism for switching between regular and wide jaw openings to accommodate different fastener sizes. The PVC-coated handles offer a comfortable, non-slip grip while the precisely machined jaws securely grasp round, flat, and hexagonal objects without slipping. Constructed from durable drop-forged carbon steel that has been heat-treated to withstand heavy daily use in professional environments. The curved jaw section grips pipes and cylindrical objects, while the flat front surface handles nuts, bolts, and sheet materials. A wire cutter integrated near the pivot handles light cutting tasks without reaching for a separate tool. Perfect for plumbing, automotive, HVAC, and general maintenance work where a single versatile gripping tool saves time.',
            'stock' => 25,
            'price' => 9.17,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers05.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'A'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Claw Hammer with Shock Reduction Grip',
            'description' => 'Ergonomic claw hammer featuring an advanced shock reduction grip system that absorbs up to 70% of impact vibrations, protecting your wrist, elbow, and shoulder during prolonged nailing sessions. The 450g carbon steel head is precision-balanced and heat-treated for maximum hardness, ensuring accurate nail driving with minimal effort on every swing. The curved claw design effortlessly removes nails without damaging surrounding wood surfaces, making it equally useful for construction and renovation work. A fiberglass-reinforced handle combines lightweight strength with exceptional durability that far outlasts traditional wooden handles. The overmolded soft-grip zone conforms to your hand shape for a custom-feeling, fatigue-free hold throughout long working days. Recommended for professional framing, carpentry, and renovation projects where operator comfort matters as much as striking performance.',
            'stock' => 25,
            'price' => 13.41,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer01.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Hammer',
            'description' => 'A dependable standard claw hammer suitable for driving and removing nails in everyday construction, carpentry, and home improvement projects. The 350g carbon steel head is drop-forged and heat-treated for maximum hardness, then fitted to a traditional wooden handle that provides natural shock absorption with each strike. Well-balanced weight distribution between the head and handle ensures accurate strikes with minimal wrist effort, reducing fatigue during repetitive nailing tasks. The polished face resists rust and provides a smooth surface that drives nails cleanly without leaving marks on finish materials. A gently curved claw provides reliable nail extraction leverage without excessive force. This versatile hammer belongs in every toolbox, from professional carpentry workshops to home garages and apartment maintenance kits.',
            'stock' => 25,
            'price' => 12.58,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Claw Hammer',
            'description' => 'Traditional claw hammer with a polished carbon steel head engineered for driving and removing nails with precision and confidence. The rubber-wrapped handle provides a secure, comfortable grip even in wet, dusty, or cold conditions where bare metal or wood handles would slip. At 500g, it offers the ideal balance between striking power and maneuverability for general carpentry, framing, and construction tasks. The curved claw design allows for smooth nail extraction without gouging or marring wood surfaces, protecting your finished work. The head-to-handle connection uses an epoxy-bonded wedge system that prevents loosening over time. A reliable, no-nonsense tool that professional carpenters and weekend builders alike reach for every day.',
            'stock' => 25,
            'price' => 11.48,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer03.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Thor Hammer',
            'description' => 'The legendary Thor Hammer combines premium craftsmanship with raw striking power in a tool that is truly built to last generations. Forged from Uru metal with a hand-wrapped leather grip, this extraordinary 5kg masterpiece delivers unmatched impact force for the most demanding demolition, forging, and heavy construction tasks. The perfectly balanced oversized head ensures every swing transfers maximum energy to the target, while the enlarged striking face covers more surface area than any conventional hammer. Despite its impressive mass, the ergonomic handle design and precise weight distribution allow for surprisingly controlled, accurate swings. This is not merely a tool but a statement piece for those who accept nothing less than the absolute best in their workshop. Comes with a lifetime warranty because true legends never fade. Please note: only one Thor Hammer is permitted per customer.',
            'stock' => 25,
            'price' => 11.14,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer04.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Sledgehammer',
            'description' => 'Powerful sledgehammer designed for heavy demolition, driving stakes, breaking concrete, and splitting masonry with devastating efficiency. The 3.6kg forged steel head delivers tremendous impact force concentrated through a hardened striking face that resists chipping and mushrooming. A 900mm fiberglass handle provides excellent shock absorption, superior reach, and the flexibility needed to generate maximum swing velocity. Anti-vibration technology embedded in the handle dampens recoil forces, significantly reducing operator fatigue during extended demolition sessions. The overmolded grip zone prevents the handle from twisting or slipping out of sweaty or gloved hands during overhead swings. Built for construction workers, landscapers, and demolition crews who demand professional-grade striking tools that perform reliably under the most punishing conditions.',
            'stock' => 25,
            'price' => 17.75,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer05.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Claw Hammer with Fiberglass Handle',
            'description' => 'Modern claw hammer featuring a durable fiberglass handle that outlasts wood, absorbs vibration more effectively, and shrugs off moisture without swelling, cracking, or loosening over time. The 400g carbon steel head is drop-forged and heat-treated to optimal hardness for reliable nail driving in softwood, hardwood, and engineered lumber. The fiberglass core is permanently bonded to the head and encased in a comfortable overmold grip that prevents slipping even when wet or oily. A magnetic nail starter near the head allows one-handed nail placement, freeing your other hand to hold materials in position. The curved claw provides smooth, controlled nail extraction with excellent leverage. Ideal for contractors and DIY builders who want a weather-resistant, maintenance-free hammer that performs reliably on every job site and in any season.',
            'stock' => 25,
            'price' => 20.14,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer06.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Court Hammer',
            'description' => 'Compact precision court hammer with a 300g carbon steel head designed for light-duty nailing, tapping dowels, and delicate assembly tasks where a full-size hammer would be too heavy and unwieldy. The short 280mm wooden handle provides excellent close-quarters control for detailed work in confined spaces such as cabinets, closets, and furniture interiors. The polished flat face drives small nails and brads without bending them, while the cross-peen on the opposite side is useful for starting small fasteners between your fingers. Perfectly weighted to provide enough force for trim carpentry and light framing without damaging delicate surfaces. The lacquered wooden handle resists moisture absorption and provides a warm, comfortable grip. A handy addition to any toolkit when precision and finesse matter more than raw striking power.',
            'stock' => 25,
            'price' => 18.63,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer07.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Wood Saw',
            'description' => 'Traditional hand saw crafted with a high carbon steel blade featuring 7 teeth per inch for efficient crosscutting and ripping of timber, plywood, and composite boards. The 500mm blade length handles standard dimensional lumber, sheet goods, and tree branches with smooth, clean cuts that require minimal sanding afterward. Each tooth is precision-set and hardened to stay sharp through hundreds of cuts in both softwood and hardwood species. A comfortable contoured wooden handle provides a secure three-finger grip and natural wrist alignment that reduces fatigue during extended cutting sessions. The taper-ground blade is thinner at the back than at the teeth, reducing friction and binding during deep cuts. Essential for carpenters, woodworkers, and gardeners who appreciate the precision, quiet operation, and portability of a quality hand saw.',
            'stock' => 25,
            'price' => 12.18,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hand Saw')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'saw01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Adjustable Wrench',
            'description' => 'Chrome vanadium steel adjustable wrench with a smooth-action worm gear jaw mechanism that opens to a generous 30mm capacity for versatile bolt, nut, and fitting work. The precision-machined worm gear ensures the movable jaw stays firmly locked in position under heavy torque loads, eliminating the slipping and rounding common with lesser tools. Laser-etched metric and imperial scales on the jaw face allow for quick size identification without a separate caliper or ruler. The slim-profile head design fits into recessed areas and tight clearances where a standard box wrench cannot reach. A corrosion-resistant chrome finish protects against rust and makes the wrench easy to wipe clean after greasy jobs. A workshop essential for plumbers, mechanics, and assembly technicians who work with multiple fastener sizes throughout the day.',
            'stock' => 25,
            'price' => 20.33,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Wrench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'wrench01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Angled Spanner',
            'description' => 'Offset angled spanner specifically designed to reach bolts, nuts, and fittings in recessed, obstructed, or deeply set locations where a straight wrench simply cannot fit or turn. Made from chrome vanadium steel with a satin-chrome finish for excellent corrosion resistance, easy cleaning, and professional appearance. The 15-degree offset angle at both ends provides ample knuckle clearance while maintaining full torque transfer to the fastener without slipping. By flipping the spanner over between turns, you can work in spaces with as little as 30 degrees of swing arc. The slim jaw profile fits into narrow gaps alongside adjacent components without interference. Indispensable for automotive mechanics working in engine bays, appliance repair technicians accessing internal components, and industrial maintenance engineers servicing tightly packed machinery.',
            'stock' => 25,
            'price' => 14.14,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Wrench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'wrench02.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Open-end Spanners (Set)',
            'description' => 'Complete professional set of 12 open-end spanners covering metric sizes from 6mm to 32mm, crafted from chrome vanadium steel for lasting strength and resistance to flexing under heavy torque. Each spanner features a slim-profile head design angled at 15 degrees for access in tight spaces, and the open-end configuration allows quick lateral placement on fasteners without the need to thread them through from the end. A mirror-polished chrome finish resists corrosion, repels grease and oil, and looks sharp in any professional tool collection. Size markings are deeply laser-etched on both sides of each spanner for instant identification even in low light conditions. The set is supplied in a durable roll-up canvas pouch with individual pockets for organized storage and easy transport to any job site. An essential investment for automotive workshops, industrial maintenance departments, and serious home mechanics.',
            'stock' => 25,
            'price' => 38.51,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Wrench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'wrench03.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Phillips Screwdriver',
            'description' => 'Professional-quality Phillips screwdriver with a PH2 tip precision-machined from chrome vanadium steel and vapor-blasted for maximum grip inside cross-head screw recesses. The bi-component handle features a soft-grip comfort zone that absorbs torque reaction and vibration, combined with a hard polypropylene core that transmits maximum turning force to the blade. A hex bolster machined into the shaft just above the handle allows a wrench to be applied for additional turning leverage on stubborn or over-tightened screws. The round blade shaft is chrome-plated for corrosion resistance and easy cleaning after use in greasy or painted environments. A hanging hole in the handle allows convenient pegboard or hook storage for quick identification and access. Suitable for electricians, cabinet makers, assembly technicians, and anyone who needs a reliable, comfortable everyday screwdriver.',
            'stock' => 25,
            'price' => 4.92,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Screwdriver')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'screwdriver01.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'A'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Mini Screwdriver',
            'description' => 'Precision mini screwdriver with a PH0 tip designed specifically for working with small screws found in electronics, eyeglasses, watches, camera equipment, and detailed hobby projects. The carbon steel blade is hardened and tempered to maintain an accurate tip profile through thousands of insertions in delicate threaded fasteners. A freely rotating end cap allows single-handed operation by applying downward pressure with your index finger while your thumb and middle finger turn the barrel. The knurled aluminum barrel provides a secure, non-slip grip for fine adjustments requiring minimal torque. At just 150mm overall length, this screwdriver fits easily in a shirt pocket, precision tool roll, or electronics repair kit. An essential tool for IT technicians, jewelers, model builders, and anyone who works with miniature components.',
            'stock' => 25,
            'price' => 13.96,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Screwdriver')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'screwdriver02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Chisels Set',
            'description' => 'Set of five professional wood chisels with chrome vanadium steel blades ground to a razor-sharp 25-degree bevel for clean, precise cuts in both hardwood and softwood species. Traditional European-style wooden handles are turned from dense beech and designed to withstand repeated mallet strikes without splitting or mushrooming at the striking end. Blade widths of 6mm, 10mm, 13mm, 19mm, and 25mm cover the full range of common woodworking tasks including mortising, paring, hinge recessing, and joint fitting. Each blade is individually honed at the factory and protected by a clear lacquer coating that should be removed before first use. Supplied in a fitted wooden storage box with individual blade slots to prevent edge contact and maintain sharpness between uses. A solid foundation set for apprentice and experienced woodworkers who value edge retention, balance, and traditional craftsmanship.',
            'stock' => 25,
            'price' => 12.96,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Chisels')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'chisels01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Wood Carving Chisels',
            'description' => 'Specialized wood carving chisels with high carbon steel blades honed to an exceptionally keen edge for detailed decorative and sculptural work in all timber species. Hand-turned ash wood handles are ergonomically shaped with a gentle palm swell that provides comfort and control during extended carving sessions without causing blisters or hand cramps. The set of four essential profiles includes a straight chisel, skew chisel, shallow gouge, and V-parting tool, covering the most frequently used carving strokes from rough shaping to fine finishing. Each blade is tang-mounted into the handle with a brass ferrule that prevents the wood from splitting under mallet impact. The blades are supplied with a factory edge suitable for immediate use, though experienced carvers may wish to refine the bevel angle to suit their personal technique. Ideal for furniture makers, sign carvers, sculptors, and woodworking hobbyists pursuing the timeless art of hand carving.',
            'stock' => 25,
            'price' => 45.23,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Chisels')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'chisels02.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Swiss Woodcarving Chisels',
            'description' => 'Premium Swiss-made woodcarving chisels forged from proprietary Swiss tool steel and fitted with hornbeam handles renowned worldwide for their exceptional density, shock resistance, and beautiful grain pattern. The set of six profiles covers every essential carving stroke from aggressive rough shaping to delicate detail work, including flat, skew, two gouge sweeps, V-parting, and spoon profiles. Each blade is individually hand-sharpened and polished at the factory by skilled craftspeople using traditional Swiss grinding techniques, resulting in a mirror-polished cutting edge ready for immediate use on any wood species. The octagonal handle shape prevents the tools from rolling off the workbench and provides a natural indexing point so you can orient the cutting edge by feel alone. Hornbeam end caps withstand years of mallet strikes without deformation. Trusted by master carvers, luthiers, and restoration specialists worldwide for over a century of uncompromising quality and cutting performance.',
            'stock' => 25,
            'price' => 22.96,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Chisels')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'chisels03.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Tape Measure 7.5m',
            'description' => 'Professional-grade 7.5-meter retractable tape measure built for daily use on construction sites, in workshops, and on renovation projects. The 25mm wide nylon-coated steel blade resists bending, kinking, and snapping at full extension, while the coating protects against abrasion and moisture that would corrode an unprotected blade. An impressive standout of over 3 meters allows solo measurements across door openings, window frames, and room widths without the blade folding. The heavy-duty rubber-armored housing absorbs drops from ladder height onto concrete floors, while the integrated belt clip and wrist lanyard keep the tape always within reach. A smooth, controllable retraction mechanism and positive blade lock hold your measurement securely in place. Dual metric and imperial markings printed in high-contrast colors ensure compatibility with any project specification and easy reading in all lighting conditions.',
            'stock' => 25,
            'price' => 7.23,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Measures')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'measure01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'A'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Measuring Tape',
            'description' => 'Standard 5-meter measuring tape with a durable 19mm steel blade and reliable automatic retraction mechanism for quick, hassle-free measurements in the workshop, on the job site, or around the home. The precision-riveted end hook features controlled play that automatically compensates for accurate inside and outside measurements, eliminating a common source of measuring error. A thumb-operated blade lock clicks firmly into place to hold your measurement while you mark, transfer dimensions, or record numbers. Clear metric graduations are printed in fade-resistant ink that remains legible even after years of heavy use and repeated blade extension cycles. The compact, lightweight ABS housing fits comfortably in a trouser pocket, tool belt pouch, or apron for all-day portability without adding noticeable weight. An affordable, dependable measuring tool that every tradesperson, crafter, and homeowner should have within arms reach.',
            'stock' => 25,
            'price' => 10.07,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Measures')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'measure02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'B'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Tape Measure 5m',
            'description' => 'Compact 5-meter tape measure designed for everyday measuring tasks around the home, workshop, and light commercial projects. The 19mm steel blade features clear metric graduations engraved and printed for readability that endures even after extensive use and repeated blade cycling. A smooth retraction spring mechanism and reliable thumb-operated blade lock ensure consistent, hassle-free operation day after day without jamming or premature blade wear. The slim, lightweight ABS housing slips easily into a pocket or clips to a belt loop, making this tape measure truly portable for all-day carry. The end hook is triple-riveted for durability and features controlled play for accurate inside and outside measurements. A great everyday carry measuring tool for interior designers, real estate professionals, DIY enthusiasts, and anyone who regularly needs quick, accurate measurements.',
            'stock' => 25,
            'price' => 12.91,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Measures')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'measure03.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Square Ruler',
            'description' => 'Precision stainless steel square ruler for checking, marking, and transferring 90-degree angles in woodworking, metalworking, and construction layout with confidence. The 300mm blade features deeply etched metric graduations filled with black ink that resist workshop wear and remain clearly readable for the lifetime of the tool. Both the inside and outside edges are precision-ground perfectly square to within 0.1mm per 300mm, meeting the accuracy requirements of fine joinery, cabinet making, and machine setup work. The stock and blade are machined from a single piece of stainless steel, eliminating riveted joints that can loosen and introduce error over time. A satin-brushed finish reduces glare under workshop lighting for comfortable visual reading at any angle. An essential layout and verification tool for carpenters, machinists, and quality-conscious makers who demand straight, true cuts and assemblies.',
            'stock' => 25,
            'price' => 15.75,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Measures')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'measure04.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Safety Goggles',
            'description' => 'Impact-resistant safety goggles with optically clear polycarbonate lenses providing UV400 protection against harmful ultraviolet radiation from welding flash, sunlight, and fluorescent lighting. The permanent anti-fog coating on the inner lens surface maintains clear, distortion-free vision even in humid, steamy, or rapidly changing temperature environments where untreated lenses would instantly cloud over. A soft PVC frame with integrated ventilation channels seals comfortably around the eyes to block airborne dust, wood chips, metal filings, and liquid splashes from reaching your eyes from any direction. The adjustable elastic headband stretches to fit over most prescription eyeglasses and hard hats without pressure points. Scratch-resistant outer lens coating extends the useful life of the goggles in abrasive workshop environments. Compliant with international safety standards for grinding, drilling, sawing, chemical handling, and laboratory work.',
            'stock' => 25,
            'price' => 24.26,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'goggles01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Safety Helmet Face Shield',
            'description' => 'Combined safety helmet and full-face shield providing comprehensive head, face, and chin protection on construction sites, in industrial workshops, and during agricultural operations. The injection-molded ABS plastic shell absorbs and distributes impact energy from falling objects, while the optical-grade polycarbonate visor guards against flying debris, hot sparks, chemical splashes, and UV radiation. An adjustable six-point ratchet headband accommodates different head sizes from 52 to 64cm and can be worn comfortably for a full eight-hour working day without pressure points. The flip-up visor design allows quick transitions between protected work and unobstructed visibility without removing the helmet. Ventilation slots in the shell promote airflow to keep the wearer cool in warm conditions. Replacement visors are available separately, extending the service life of the complete assembly well beyond that of a single-piece face shield.',
            'stock' => 25,
            'price' => 35.62,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'goggles02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Protective Gloves',
            'description' => 'Heavy-duty leather work gloves engineered to deliver an optimal combination of cut resistance, abrasion protection, and manual dexterity for demanding industrial and construction tasks. The full-grain cowhide leather palm provides excellent grip and wear resistance when handling rough timber, sharp metal edges, concrete blocks, and hot materials. Reinforced fingertips and a padded knuckle guard protect the most vulnerable areas of the hand and significantly extend the service life of the gloves under punishing daily conditions. An elastic wrist closure keeps debris out and holds the glove securely in place during vigorous activity. The leather softens and conforms to your hand shape with use, providing an increasingly comfortable custom fit over time. Available in size L, these gloves are suitable for construction, carpentry, metalwork, landscaping, and general workshop duties.',
            'stock' => 25,
            'price' => 21.42,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'gloves01.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Super-thin Protection Gloves',
            'description' => 'Disposable nitrile examination gloves offering reliable chemical resistance and outstanding tactile sensitivity in a super-thin 0.1mm wall thickness for tasks requiring fine finger dexterity. The textured fingertips provide dependable grip on small parts, wet surfaces, oily components, and smooth materials that bare skin or thicker gloves would struggle to hold. Powder-free, latex-free construction eliminates the risk of powder contamination on sensitive work surfaces and significantly reduces the chance of allergic reactions common with natural rubber gloves. The ambidextrous design fits either hand, reducing waste when only one glove is needed for a quick task. Each glove is beaded at the cuff for easy donning and reliable stay-on performance. Box of 50 gloves in size M, perfect for painting, staining, adhesive application, precision assembly, first aid, and laboratory work.',
            'stock' => 25,
            'price' => 38.45,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'gloves02.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Construction Helmet',
            'description' => 'Ventilated high-density polyethylene construction helmet designed to protect against falling objects, lateral impacts, and electrical contact hazards on active building sites and industrial facilities. The six-point textile suspension system distributes impact forces evenly across the top of the head, while a foam comfort pad absorbs perspiration and provides cushioning for all-day wearability. An adjustable ratchet headband accommodates head sizes from 52 to 64cm with one-handed adjustment, even while wearing gloves. Four integrated ventilation slots promote cooling airflow to keep the wearer comfortable and alert during hot-weather work without compromising the structural integrity of the shell. Accessory slots on both sides accept clip-on ear defenders, face shields, and headlamps for job-specific customization. Meets international safety standards for industrial head protection across construction, mining, utility, and infrastructure projects.',
            'stock' => 25,
            'price' => 41.29,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'helmet01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Ear Protection',
            'description' => 'Professional-grade over-ear hearing protectors providing 28 decibels of noise reduction rating for preserving hearing health in loud workshop, factory, and construction environments. Plush foam-filled ear cushions create a comfortable, pressure-free acoustic seal around the ears that blocks harmful noise while still allowing normal conversation at close range. The adjustable padded headband accommodates all head sizes and can be worn over a hard hat or directly on the head depending on the work environment. Lightweight ABS plastic ear cup shells are durable enough to withstand daily tossing into a toolbox or hanging from a belt hook without cracking. The wide headband distributes clamping pressure evenly, eliminating the discomfort and headaches associated with cheaper, tighter-fitting ear muffs. Essential hearing protection when operating power saws, routers, impact drivers, grinders, compressors, or working near any equipment exceeding 85 decibels.',
            'stock' => 25,
            'price' => 18.58,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'earprotection01.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Screws',
            'description' => 'Box of 100 general-purpose pan head machine screws in M5x30mm size, manufactured from zinc-plated mild steel for reliable corrosion resistance in indoor and sheltered outdoor applications. The Phillips drive recess is precisely formed to provide a secure, positive engagement with the screwdriver tip, significantly reducing the cam-out that damages both screws and tools. The pan head profile sits above the surface of the material, providing a clean, finished appearance and a bearing surface that distributes clamping force over a wider area than flat-head alternatives. Machine-threaded shanks ensure consistent, repeatable clamping force across all fastened joints and allow for easy removal and reinstallation during maintenance. Compatible with M5 hex nuts, nylon lock nuts, and threaded inserts. Suitable for joining sheet metal, machine assembly, wood-to-metal connections, and general workshop fabrication tasks.',
            'stock' => 25,
            'price' => 6.25,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Nuts and bolts',
            'description' => 'Comprehensive assorted set of 200 stainless steel nuts and bolts in popular metric sizes ranging from M3 to M10, providing a one-stop selection for repair, assembly, prototyping, and maintenance tasks across all trades. The 18-8 austenitic stainless steel composition delivers excellent corrosion resistance for both indoor and outdoor applications, including marine, food-processing, and chemical environments where carbon steel would quickly deteriorate. Hex bolt heads and matching hex nuts are manufactured to DIN standard tolerances for a precise, wobble-free thread engagement that inspires confidence in critical fastened joints. The set includes matching flat washers and spring washers for each size to ensure proper load distribution and vibration resistance. Supplied in a compartmentalized clear plastic case with labeled dividers for instant size identification and organized storage. An invaluable workshop consumable that eliminates trips to the hardware store for common fastener sizes.',
            'stock' => 25,
            'price' => 5.55,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cross-head screws',
            'description' => 'Pack of 50 Phillips cross-head screws in M4x25mm size, manufactured from zinc-plated steel for dependable corrosion protection in indoor and semi-exposed environments. The cross-head drive pattern ensures efficient power transfer from both manual and powered screwdrivers, while the precisely formed recess depth minimizes cam-out and reduces the risk of damaging the screw head or work surface. A sharp, self-starting thread point eliminates the need for pre-drilling in softwood, MDF, and chipboard applications, speeding up assembly time considerably. The 25mm thread length provides strong pull-out resistance in materials 15mm thick and above. Ideal for fastening light-gauge metal brackets, cabinet hinges, electrical outlet boxes, and general hardware. Each screw is individually inspected to ensure consistent head formation and thread quality across the entire pack.',
            'stock' => 25,
            'price' => 7.99,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners03.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Flat-Head Wood Screws',
            'description' => 'Pack of 75 countersink flat-head wood screws in 4x40mm size, crafted from yellow zinc-plated steel for enhanced moisture resistance in both interior and sheltered exterior woodworking applications. The 82-degree countersink flat head sits perfectly flush with the timber surface for a clean, professional finish that can be painted, stained, or filled invisibly. Deep single-lead threads are designed to bite aggressively into both hardwood and softwood fibers for superior holding power that resists pull-out under load and vibration. The sharp gimlet point self-starts without pre-drilling in most softwoods, while a pilot hole is recommended for hardwoods to prevent splitting near edges. The yellow zinc coating provides a warm color that blends naturally with light-colored timbers. Perfect for furniture assembly, deck building, cabinet construction, and general woodworking joinery where appearance matters.',
            'stock' => 25,
            'price' => 3.95,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners04.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'M4 Nuts',
            'description' => 'Box of 100 M4 hexagonal nuts manufactured from A2-grade austenitic stainless steel for excellent resistance to rust, tarnishing, and corrosion in demanding indoor and outdoor environments. The precisely machined internal threads ensure smooth, easy engagement with M4 bolts, machine screws, and threaded rod without cross-threading or binding. The standard DIN 934 hex profile is compatible with 7mm sockets, open-end spanners, and adjustable wrenches for convenient tightening with any common hand or power tool. Chamfered edges on both faces allow the nut to start on the bolt thread from either direction, saving time during repetitive assembly operations. Consistent dimensional accuracy across all 100 pieces means reliable torque values and clamping forces in every joint. Essential workshop consumables for assembly, prototyping, equipment repair, and maintenance projects across mechanical, electrical, and general engineering trades.',
            'stock' => 25,
            'price' => 4.65,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners05.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Washers',
            'description' => 'Assorted pack of 150 zinc-plated steel flat washers in popular metric sizes ranging from M4 to M10, covering the most commonly needed sizes for workshop, garage, and job site fastener work. These washers distribute the clamping force of bolts and nuts over a larger surface area, preventing damage to soft materials like wood, plastic, and painted or powder-coated metal surfaces. The smooth, burr-free finish on both faces ensures a flat, stable seating surface that maximizes friction and prevents fastener loosening under vibration. Each washer is stamped from precision-rolled steel strip to consistent thickness tolerances, ensuring predictable bolt stretch and joint behavior across multiple fastening points. The zinc plating provides reliable corrosion protection for indoor use and sheltered outdoor applications. An indispensable consumable for any workshop, garage, or maintenance department handling regular fastener assembly and repair work.',
            'stock' => 25,
            'price' => 3.55,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners06.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Drawer Tool Cabinet',
            'description' => 'Professional rolling tool cabinet featuring five full-extension ball-bearing drawer slides rated for smooth, quiet, jam-free operation even when loaded to their 25kg per-drawer capacity. Constructed entirely from cold-rolled 18-gauge steel with a durable powder-coated finish that resists scratches, workshop chemicals, solvent splashes, and surface rust. Each drawer includes textured anti-slip liners to keep tools organized and prevent them from sliding around during transport between work areas. A central keyed cylinder lock simultaneously secures all five drawers, protecting your valuable tool investment from unauthorized access on shared job sites. The 680x460x1000mm cabinet rolls on four swivel casters with brakes, allowing easy repositioning around any workshop or garage floor. With a combined 200kg load capacity and a solid steel worktop surface, this cabinet serves as both mobile storage and an auxiliary work surface.',
            'stock' => 25,
            'price' => 89.55,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Storage Solutions')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'toolcabinet02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Tool Cabinet',
            'description' => 'Versatile wall-mounted or freestanding tool cabinet built from heavy-gauge welded steel with three spacious drawers and an upper compartment for organized, secure tool storage. The textured powder-coated exterior resists workshop chemicals, oil stains, and everyday bumps and scrapes, maintaining a professional appearance through years of hard use. A keyed cylinder lock secures the entire cabinet, keeping expensive hand tools and precision instruments safe from unauthorized access in shared workshops. Adjustable drawer dividers allow you to customize compartment sizes for different tool collections, from small precision instruments to bulky power tool accessories. Pre-drilled mounting holes on the back panel allow quick, secure wall attachment using standard lag screws or wall anchors. A compact 600x400x800mm footprint makes this cabinet ideal for smaller workshops, garage walls, service vans, and mobile repair stations.',
            'stock' => 25,
            'price' => 86.71,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Storage Solutions')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'toolcabinet01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Workbench with Drawers',
            'description' => 'Heavy-duty workbench combining a solid hardwood laminate top with an integrated steel frame that supports working loads up to 500kg for the toughest assembly, fabrication, and repair tasks. Four full-extension lockable drawers on ball-bearing slides provide convenient storage for hand tools, fasteners, measuring instruments, and project components within easy arms reach. The 1500x600mm work surface offers generous space for clamping, assembly, soldering, and detailed work, while the hardwood top resists dents, scratches, and chemical spills better than particleboard alternatives. A lower shelf provides additional storage for power tools, parts bins, and heavy equipment. Adjustable leveling feet compensate for uneven workshop floors, ensuring a perfectly stable, wobble-free work platform. The ideal centerpiece for professional workshops, maker spaces, and serious home garages where a reliable work surface is essential.',
            'stock' => 25,
            'price' => 178.20,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Workbench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'workbench02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Wooden Workbench',
            'description' => 'Traditional solid European beech wood workbench built with time-tested mortise-and-tenon joinery and hardwood dowel pins for exceptional rigidity and a working lifetime measured in decades rather than years. The 1400x500mm work surface is planed smooth, sanded to a fine finish, and treated with penetrating linseed oil to resist moisture, glue, and common workshop stains without building up a slippery film. A built-in tool well running the full length of the back edge keeps chisels, pencils, marking gauges, and small parts from rolling off the work surface during use. The substantial underframe includes a full-width lower shelf for storing planes, saws, and workshop accessories within easy reach. Rated for a 300kg distributed load capacity, this bench handles heavy timber, metalwork clamping, and power tool operations without flexing. The timeless design and natural beauty of solid beech make this workbench both a serious working tool and the proud centerpiece of any craft workshop.',
            'stock' => 25,
            'price' => 172.52,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Workbench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'workbench01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Leather toolbelt',
            'description' => 'Professional-grade tool belt handcrafted from full-grain saddle leather that develops a rich, distinctive patina with use while actually growing stronger and more supple over time. Eleven pockets, loops, and holders are specifically sized and positioned for quick access to hammers, tape measures, pliers, screwdrivers, nail sets, pencils, and assorted fasteners without looking down. The adjustable waist strap fits waist sizes from 32 to 44 inches comfortably over heavy work clothing and distributes weight evenly across the hips to minimize lower back strain during long working days. Double-stitched seams with heavy waxed nylon thread and copper-riveted stress points ensure this belt endures years of daily professional use on construction sites, rooftops, and renovation projects. The leather is vegetable-tanned without harsh chemicals, making it both environmentally responsible and pleasantly aromatic. An heirloom-quality working tool that many tradespeople pass down through generations of continued daily service.',
            'stock' => 25,
            'price' => 61.16,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Tool Belts')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'toolbelt01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Sheet Sander',
            'description' => 'Electric sheet sander delivering 12,000 orbits per minute for fast, controlled surface finishing on wood, painted surfaces, metal, and composite materials. The 240W motor provides consistent sanding power from rough 60-grit material removal through to ultra-fine 240-grit finishing, all using standard 112x102mm sanding sheets that are widely available and economical. An integrated dust collection port connects directly to a shop vacuum for a virtually dust-free work environment, or the included cloth dust bag captures fine particles when vacuum extraction is not available. The paper clamping system accepts both pre-punched and plain sanding sheets, giving you maximum flexibility in abrasive selection. At just 1.8kg, this sander is light enough for extended one-handed use on vertical surfaces, overhead work, and detailed finishing tasks. Low vibration levels and an ergonomic palm-grip design reduce hand fatigue, making this tool a pleasure to use for beginners and professionals alike.',
            'stock' => 25,
            'price' => 58.48,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Sander')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'sander01.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Belt Sander',
            'description' => 'Powerful 800W belt sander engineered for aggressive material removal on large flat surfaces including tabletops, doors, hardwood flooring, and timber decking. The 76x533mm sanding belt travels at an impressive 380 meters per minute, enabling rapid stock removal, paint stripping, and surface leveling in a fraction of the time required by orbital sanders. A precision front roller nose allows flush sanding against walls, inside corners, and perpendicular surfaces for complete coverage without leaving unsanded edges. The quick-release belt change mechanism allows swapping abrasive grits in seconds without tools, keeping workflow interruptions to a minimum. A secondary handle on the front provides two-handed control and even pressure distribution across the full width of the belt. The dust extraction bag and auxiliary vacuum port work together to capture the large volume of fine particles generated during aggressive sanding operations.',
            'stock' => 25,
            'price' => 73.59,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Sander')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'sander02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Circular Saw',
            'description' => 'Portable circular saw powered by a robust 1400W motor that drives a 190mm carbide-tipped blade at 5,500 RPM for clean, fast, straight cuts through solid timber, plywood, MDF, and sheet materials up to 67mm thick. The precision-machined aluminum base plate provides a flat, stable reference surface and adjusts smoothly for bevel cuts from 0 to 45 degrees with a positive detent at the most commonly used angles. An integrated dust blower directs a stream of air ahead of the blade to keep the pencil line visible under the constant shower of sawdust generated during cutting. The spindle lock allows quick, safe blade changes with a single wrench, while the electric brake stops the blade within seconds of releasing the trigger for added safety between cuts. An ergonomic soft-grip main handle and auxiliary front handle provide confident two-handed control for both plunge cuts and guided rip cuts. Essential for framing carpenters, renovation contractors, and DIY builders who need a portable, powerful saw for on-site timber work.',
            'stock' => 25,
            'price' => 80.19,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Saw')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'saw02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Random Orbit Sander',
            'description' => 'Electric random orbit sander combining 14,000 RPM pad rotation with an eccentric 2.5mm orbital action pattern that produces a completely swirl-free finish on wood, lacquer, paint, and primer surfaces. The 125mm hook-and-loop pad accepts standard multi-hole sanding discs from 60 to 400 grit for quick, tool-free abrasive changes between roughing and finishing passes. A responsive 300W motor delivers consistent sanding power at every speed setting, from aggressive stock removal at full speed to delicate final finishing at reduced RPM using the variable speed dial. The random orbital motion means no two particles of abrasive ever follow the same path twice, eliminating the visible scratch patterns that plague standard orbital and belt sanders. The integrated micro-filter dust collection system captures over 90% of airborne particles at the source. Low vibration levels and an ergonomic palm-grip body make this the preferred finishing tool for furniture makers, cabinet shops, and automotive paint preparation.',
            'stock' => 25,
            'price' => 100.79,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Sander')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'sander03.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cordless Drill 20V',
            'description' => 'Versatile 20V cordless drill and driver featuring a high-capacity 4.0Ah lithium-ion battery that delivers sustained power for a full day of demanding drilling and fastening tasks on a single charge. The heavy-duty 13mm single-sleeve keyless chuck accepts a comprehensive range of drill bits, spade bits, hole saws, and driver accessories without the need for a chuck key. A two-speed all-metal gearbox provides 450 RPM in low gear for maximum torque fastening and 1,800 RPM in high gear for rapid drilling in wood, metal, and masonry materials. Twenty-plus-one torque settings on the adjustable clutch collar allow precise control over fastening depth to prevent cam-out and screw damage in different materials. An integrated LED work light automatically illuminates the drilling area when the trigger is touched, providing visibility in dark cabinets, ceiling cavities, and crawl spaces. Ideal for contractors, remodelers, and serious DIY enthusiasts who need portable, professional-grade power without being tethered to an extension cord.',
            'stock' => 25,
            'price' => 125.23,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'drill01.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cordless Drill 24V',
            'description' => 'Professional-grade 24V cordless drill delivering an impressive 80Nm of peak torque through a heavy-duty all-metal two-speed gearbox designed for the most demanding commercial drilling and fastening applications. The 5.0Ah high-capacity lithium-ion battery provides extended runtime that gets professional users through a full working day of continuous drilling, driving, and impacting without interruption. A 13mm all-metal single-sleeve keyless chuck ensures reliable, concentric bit retention even under the high torque loads generated during large-diameter drilling and long fastener driving. Twenty-plus-one clutch torque settings provide precise depth and torque control across the full range of materials from delicate cabinetry to structural timber framing. The brushless motor technology extends tool life, increases runtime, and generates less heat than brushed alternatives under sustained heavy loads. Built for trade professionals in construction, electrical, plumbing, and HVAC who demand maximum performance, durability, and battery endurance from their primary cordless tool.',
            'stock' => 25,
            'price' => 66.54,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'drill02.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cordless Drill 18V',
            'description' => 'Compact and lightweight 18V cordless drill offering 50Nm of torque in a well-balanced package that reduces operator fatigue during extended overhead, horizontal, and downward drilling and driving tasks. The 3.0Ah lithium-ion battery provides ample capacity for a busy day of residential drilling and driving, while the fast charger replenishes a depleted battery in under 60 minutes. A 10mm keyless chuck handles the most commonly used drill bits, spade bits, and quarter-inch hex driver inserts for everyday workshop and job site tasks. The ergonomic soft-grip handle and compact head length provide comfortable, controlled operation in tight spaces where larger drills cannot reach. An integrated LED work light illuminates the work surface for improved accuracy when driving screws in dimly lit areas. A great all-around cordless drill for home improvement, furniture assembly, deck building, and light-duty trade work where portability and comfort are priorities.',
            'stock' => 25,
            'price' => 119.24,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'drill03.avif')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cordless Drill 12V',
            'description' => 'Lightweight 12V cordless drill perfect for light-duty drilling, screw driving, and assembly tasks in wood, drywall, plastic, and thin sheet metal. The ultra-compact design weighs just 1.2kg with the 2.0Ah battery installed, making it exceptionally easy to maneuver and control in tight spaces like inside cabinets, closets, junction boxes, and overhead ceiling cavities where heavier drills cause premature fatigue. The tool delivers up to 30Nm of torque through a 10mm keyless chuck, providing adequate power for the vast majority of household and light commercial fastening applications. Fifteen-plus-one clutch torque positions give you precise control over fastening depth and prevent over-tightening in delicate materials like softwood trim and melamine shelving. A quick-charging system brings the battery from empty to full in approximately 45 minutes, minimizing downtime between tasks. An excellent first cordless drill for homeowners, apartment dwellers, and hobbyists, and a convenient secondary tool for professionals who want a light, compact option alongside their heavy-duty driver.',
            'stock' => 25,
            'price' => 46.50,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'drill04.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false,
            'co2_rating' => 'C'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Excavator',
            'description' => 'Heavy-duty tracked excavator available for hourly rental, designed for professional foundation digging, trenching, utility installation, and general earthmoving operations on residential and commercial construction sites. The powerful hydraulic system delivers smooth, responsive boom, arm, and bucket controls for precise positioning and efficient material handling in both tight urban spaces and open excavation sites. An enclosed climate-controlled cab provides operator comfort during long shifts, with excellent forward, side, and rear visibility through large laminated safety-glass windows and an integrated rearview camera system. The rubber-tracked undercarriage minimizes ground disturbance on finished surfaces while providing stable, sure-footed mobility on slopes, soft ground, and uneven terrain. Quick-coupling hydraulic connections allow rapid attachment changes between digging buckets, grading blades, and hydraulic breakers without leaving the cab. Suitable for projects ranging from swimming pool excavation and basement digging to municipal utility trenching and site grading operations.',
            'stock' => null,
            'price' => 136.50,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'excavator01.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => true,
            'co2_rating' => 'D'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Bulldozer',
            'description' => 'Robust tracked bulldozer available for hourly rental, built for heavy-duty grading, site clearing, land leveling, and large-volume earthmoving operations on construction and infrastructure projects. The wide-blade dozer pushes enormous volumes of soil, sand, gravel, and debris with ease, while the optional rear-mounted ripper attachment fractures compacted ground, asphalt, and embedded tree roots ahead of the blade pass. Ergonomic joystick cab controls and a fully automatic hydrostatic transmission allow operators of all experience levels to work efficiently and precisely from the first hour of rental. The enclosed cab features climate control, a suspension seat, and panoramic visibility for comfortable, fatigue-free operation during extended shifts. Integrated GPS rough-grading guidance is available as an optional add-on for projects requiring precise elevation control. Ideal for subdivision site preparation, road subgrade construction, agricultural land reclamation, and large-scale commercial landscaping projects.',
            'stock' => null,
            'price' => 147.50,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'bulldozer01.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => true,
            'co2_rating' => 'D'
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Crane',
            'description' => 'Mobile hydraulic crane available for hourly rental, purpose-engineered for lifting, positioning, and placing heavy loads on active construction sites, industrial facilities, and infrastructure projects. The multi-section telescoping boom extends smoothly to reach elevated installation positions while the four-point hydraulic outrigger system provides a rock-solid, perfectly level base on uneven, sloped, or soft terrain. The precision proportional load control system allows millimeter-accurate positioning of suspended loads, while the rated capacity indicator and automatic overload cutoff ensure every lift is performed safely within the machines certified working limits. The fully enclosed operators cab features ergonomic controls, climate management, and unobstructed boom-tip visibility for confident operation in all weather conditions. Extensive safety systems including anti-two-block, swing-limit, and wind-speed monitoring protect both the operator and surrounding workers on busy sites. Perfect for structural steel erection, precast concrete placement, HVAC rooftop installation, and any material handling task that exceeds the reach or capacity of standard jobsite equipment.',
            'stock' => null,
            'price' => 153.50,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'crane01.avif')->first()->id,
            'is_location_offer' => true,
            'is_rental' => true,
            'co2_rating' => 'D'
        ]
        ]);

        // Update CO2 ratings based on category
        foreach ($categoryRatings as $categoryName => $rating) {
            $categoryId = DB::table('categories')->where('name', '=', $categoryName)->value('id');
            if ($categoryId) {
                DB::table('products')->where('category_id', $categoryId)->update(['co2_rating' => $rating]);
            }
        }
    }
}
