# Secure Agent Registration System Guide

## Overview
The agent registration system ensures that only pre-approved, designated agents can register to manage employees on the platform. This prevents unauthorized access and maintains system integrity.

## How It Works

### 1. **Pre-Approved Registration Codes**
- Each agent is assigned a unique registration code by the company
- Codes are stored in the `agent_registration_codes` table
- Each code is linked to a specific agent ID
- Codes can be active, used, or revoked

### 2. **Registration Process**
1. Agent receives registration code and assigned ID from company
2. Agent visits `agent_register.php`
3. Agent enters:
   - Registration code (provided by company)
   - Agent ID (assigned by company)
   - Personal details (name, phone, email)
   - Password
4. System validates:
   - Registration code exists and is active
   - Agent ID matches the code
   - Agent ID is not already registered
   - Email is not already used
5. If valid, agent is registered and code is marked as "used"

### 3. **Security Features**

#### **Code Validation**
- Only active codes can be used
- Each code can only be used once
- Agent ID must match the code exactly
- Invalid codes show generic error message

#### **Database Security**
- Registration codes stored in separate table
- Codes are marked as "used" after registration
- Audit trail with timestamps
- Unique constraints prevent duplicates

#### **Access Control**
- Clear messaging that only authorized agents can register
- Contact information for requesting agent status
- Professional error messages

## Database Structure

### `agent_registration_codes` Table
```sql
CREATE TABLE `agent_registration_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `agent_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','used','revoked') DEFAULT 'active',
  `assigned_to` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `agent_id` (`agent_id`)
);
```

### Sample Codes (included in database)
- `AGENT2024` → Agent ID: 1001 (John Doe)
- `HOUSEHELP2024` → Agent ID: 1002 (Jane Smith)
- `CONNECT2024` → Agent ID: 1003 (Mike Johnson)
- `SECURE2024` → Agent ID: 1004 (Sarah Wilson)
- `TRUST2024` → Agent ID: 1005 (David Brown)

## Admin Management

### Admin Panel (`admin_manage_agents.php`)
- View all registration codes and their status
- Add new registration codes
- Monitor registered agents
- View statistics

### Features
- **Code Management**: Add/revoke registration codes
- **Agent Monitoring**: View all registered agents
- **Statistics**: Track usage and activity
- **Security**: Admin-only access required

## Implementation Steps

### 1. **Setup Database**
```sql
-- Import the complete househelp_db.sql file
-- This includes all tables including agent_registration_codes
```

### 2. **Configure Admin Access**
- Implement proper admin authentication
- Secure admin panel access
- Set up admin login system

### 3. **Distribute Codes**
- Generate unique codes for each agent
- Assign specific agent IDs
- Provide codes securely to agents

### 4. **Monitor Usage**
- Use admin panel to track registrations
- Monitor code usage
- Manage active/inactive codes

## Security Best Practices

### **Code Management**
- Use strong, unique codes
- Limit code distribution
- Revoke unused codes
- Regular code rotation

### **Access Control**
- Implement proper admin authentication
- Secure admin panel
- Log all admin actions
- Regular security audits

### **Data Protection**
- Encrypt sensitive data
- Regular backups
- Monitor for suspicious activity
- Implement rate limiting

## Troubleshooting

### **Common Issues**

1. **"Invalid registration code"**
   - Check if code exists in database
   - Verify code status is 'active'
   - Ensure code hasn't been used

2. **"Agent ID does not match"**
   - Verify agent ID matches the code
   - Check for typos in ID entry
   - Confirm with admin

3. **"Agent ID already exists"**
   - Code may have been used already
   - Contact admin for new code
   - Check if agent already registered

### **Admin Actions**
- Add new codes for new agents
- Revoke compromised codes
- Reset used codes if needed
- Monitor registration activity

## Contact Information
For agent registration requests or technical support:
- **Email**: admin@househelp.info
- **System**: Use admin panel for code management
- **Security**: Report any security concerns immediately

---

**Note**: This system ensures only authorized company agents can register and manage employees, maintaining the integrity and security of the platform. 