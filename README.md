# 🐟 Bangkero & Fishermen Association - Information Management System

A professional web-based information management system for the Bangkero & Fishermen Association of Olongapo City, Philippines.

## 🌟 Features

- **Dashboard** - Real-time statistics and overview
- **Events Management** - Upcoming and past events with countdown timers
- **Announcements** - Latest news and updates
- **Awards & Recognition** - Professional awards showcase ⭐ NEW
- **Resources** - Member resources and documents
- **Contact** - Communication and inquiry system
- **Responsive Design** - Works on all devices

## 🏆 NEW: Awards & Recognition Module

A complete, professional awards management system has been added!

### Quick Start
1. **Setup Database**:
   ```
   http://localhost/bangkero_system/config/setup_awards.php
   ```

2. **View Awards Page**:
   ```
   http://localhost/bangkero_system/index/home/awards.php
   ```

3. **Manage Awards** (Admin):
   ```
   http://localhost/bangkero_system/admin/manage_awards.php
   ```

### Documentation
- 📘 **[Quick Start Guide](QUICK_START_AWARDS.md)** - Get started in 3 minutes
- 📗 **[Complete Guide](AWARDS_PAGE_GUIDE.md)** - Full documentation
- 📙 **[Implementation Summary](AWARDS_IMPLEMENTATION_SUMMARY.md)** - Visual overview
- 📕 **[Visual Architecture](AWARDS_VISUAL_ARCHITECTURE.md)** - Diagrams & flows
- 📦 **[Delivery Package](DELIVERY_PACKAGE.md)** - What was delivered

## 🚀 Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Modern web browser

### Setup
1. Clone or download this repository to your web server directory
2. Import the database schema (if provided)
3. Configure database connection in `config/db_connect.php`
4. Run the awards setup script (optional)
5. Access the system through your web browser

## 🎨 Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5.3.3
- **Backend**: PHP (native, no framework)
- **Database**: MySQL
- **Icons**: Bootstrap Icons
- **Fonts**: Inter, Poppins (Google Fonts)

## 📁 Project Structure

```
bangkero_system/
├── admin/              # Admin panel
├── config/             # Configuration files
├── css/                # Stylesheets
├── images/             # Image assets
├── index/              # Main application
│   └── home/           # User-facing pages
├── uploads/            # User-uploaded files
├── vendor/             # Composer dependencies
└── *.md                # Documentation files
```

## 🔒 Security Features

- Prepared SQL statements (prevents SQL injection)
- Input sanitization and validation
- XSS protection with `htmlspecialchars()`
- Secure session management
- Type validation and casting

## 🎯 Key Pages

| Page | Path | Description |
|------|------|-------------|
| Home | `/index/home/user_home.php` | Dashboard with statistics |
| Events | `/index/home/events.php` | Event listings and details |
| Announcements | `/index/home/announcement.php` | Latest announcements |
| Awards | `/index/home/awards.php` | Awards & recognition ⭐ |
| Resources | `/index/home/resources.php` | Member resources |
| Contact | `/index/home/contact_us.php` | Contact form |

## 📱 Responsive Design

The system is fully responsive and optimized for:
- 🖥️ Desktop (≥992px)
- 📱 Tablet (768-991px)
- 📱 Mobile (<768px)

## 🎨 Design System

### Color Palette
- **Primary**: `#2c3e50` (Dark Blue)
- **Secondary**: `#34495e` (Slate)
- **Gold**: `#d4af37` (Award Accent)
- **Success**: `#27ae60` (Green)
- **Info**: `#3498db` (Blue)
- **Background**: `#f8f9fa` (Light Gray)

### Typography
- **Headings**: Poppins (Bold 700-800)
- **Body**: Inter (Regular 400-600)

## 👥 About

The Bangkero & Fishermen Association is a community-driven organization dedicated to supporting local fishermen and their families in Olongapo City. Founded in 2009, the association promotes sustainable fishing practices, strengthens unity among members, and provides opportunities for growth and livelihood development.

## 📞 Support

For issues or questions related to:
- **General system**: Contact your system administrator
- **Awards module**: Check the documentation files listed above
- **Technical issues**: Review the troubleshooting sections in guides

## 📄 License

This system is developed for the Bangkero & Fishermen Association of Olongapo City.

## 🔄 Recent Updates

### February 2026
- ✅ Fixed dashboard layout imbalance
- ✅ Added complete Awards & Recognition system
- ✅ Created admin panel for awards management
- ✅ Added comprehensive documentation
- ✅ Improved responsive design

## 🚧 Maintenance

For backup instructions, see: [DEFENSE_BACKUP_GUIDE.md](DEFENSE_BACKUP_GUIDE.md)

---

**🐟 Bangkero & Fishermen Association**  
*Supporting our fishing community since 2009*