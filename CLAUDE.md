# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repository uses a **spec-driven development workflow** system for structured feature development and bug fixing. The workflow emphasizes systematic planning, documentation, and atomic implementation with built-in validation at each phase.

## Workflow System Architecture

### Core Workflows

1. **Spec Workflow** (`/spec-create`, `/spec-execute`, `/spec-status`, `/spec-list`)
   - Structured feature development through Requirements → Design → Tasks → Implementation phases
   - Each phase requires explicit user approval before proceeding
   - Atomic task breakdown with requirement traceability
   - Automatic validation using specialized agents

2. **Bug Workflow** (`/bug-create`, `/bug-analyze`, `/bug-fix`, `/bug-verify`, `/bug-status`)
   - Systematic bug documentation, root cause analysis, and resolution
   - Phase-based approach with explicit approvals between phases
   - Verification step to confirm fix effectiveness

### Hierarchical Context System

The workflow uses **steering documents** to provide persistent project context:

- **product.md**: Product vision, target users, key features, business objectives, success metrics
- **tech.md**: Technology stack, architecture patterns, dependencies, technical constraints, decision rationale
- **structure.md**: Directory organization, naming conventions, code organization patterns, module boundaries

These documents are created via `/spec-steering-setup` and referenced throughout all workflow phases.

### Workflow Commands

The repository uses `claude-code-spec-workflow` NPX scripts for context management:

```bash
# Load steering documents context
claude-code-spec-workflow get-steering-context

# Load specification templates
claude-code-spec-workflow get-template-context spec

# Load specific spec documents
claude-code-spec-workflow get-spec-context {feature-name}

# Get task details
claude-code-spec-workflow get-tasks {feature-name} {task-id} --mode single

# Mark task complete
claude-code-spec-workflow get-tasks {feature-name} {task-id} --mode complete

# Generate individual task commands
claude-code-spec-workflow generate-task-commands {feature-name}
```

## Directory Structure

```
.claude/
├── agents/                    # Specialized validation agents
│   ├── spec-requirements-validator.md
│   ├── spec-design-validator.md
│   ├── spec-task-validator.md
│   └── spec-task-executor.md
├── bugs/                      # Bug reports and analysis
├── commands/                  # Slash commands for workflows
│   ├── spec-create.md        # Create feature specifications
│   ├── spec-execute.md       # Execute implementation tasks
│   ├── spec-status.md        # Check specification progress
│   ├── spec-list.md          # List all specifications
│   ├── bug-create.md         # Document new bugs
│   ├── bug-analyze.md        # Analyze bug root causes
│   ├── bug-fix.md            # Implement bug fixes
│   ├── bug-verify.md         # Verify bug resolution
│   └── spec-steering-setup.md # Setup project context
├── specs/                     # Feature specifications
│   └── {feature-name}/       # One directory per feature
│       ├── requirements.md   # User stories, acceptance criteria
│       ├── design.md         # Technical design, architecture
│       └── tasks.md          # Atomic implementation tasks
├── steering/                  # Persistent project context
│   ├── product.md            # Product vision and objectives
│   ├── tech.md               # Technology stack and patterns
│   └── structure.md          # Code organization conventions
└── templates/                 # Document templates
    ├── requirements-template.md
    ├── design-template.md
    ├── tasks-template.md
    ├── product-template.md
    ├── tech-template.md
    ├── structure-template.md
    ├── bug-report-template.md
    ├── bug-analysis-template.md
    └── bug-verification-template.md
```

## Spec-Driven Development Process

### Phase 1: Requirements (via `/spec-create`)

1. Create `.claude/specs/{feature-name}/requirements.md`
2. Load steering context once at beginning
3. Analyze existing codebase for similar patterns
4. Generate requirements using template structure
5. Validate using `spec-requirements-validator` agent
6. Get explicit user approval before proceeding

**Requirements Format:**
- User stories: "As a [role], I want [feature], so that [benefit]"
- Acceptance criteria: EARS format (WHEN/IF/THEN statements)
- Alignment with product.md vision

### Phase 2: Design (via `/spec-create`)

1. Load requirements.md context
2. Conduct mandatory codebase research to identify reusable patterns
3. Create design.md following template with Mermaid diagrams
4. Ensure alignment with tech.md standards and structure.md conventions
5. Validate using `spec-design-validator` agent
6. Get explicit user approval before proceeding

### Phase 3: Tasks (via `/spec-create`)

1. Load requirements.md and design.md context
2. Break design into atomic tasks following strict criteria:
   - **File Scope**: 1-3 related files maximum
   - **Time Boxing**: 15-30 minutes per task
   - **Single Purpose**: One testable outcome
   - **Specific Files**: Exact files to create/modify
   - **Agent-Friendly**: Clear input/output
3. Each task must reference specific requirements
4. Validate using `spec-task-validator` agent
5. Get user approval
6. Optionally generate individual task commands

### Phase 4: Implementation (via `/spec-execute`)

1. Execute ONE task at a time
2. Load steering context + spec context + task details
3. Use `spec-task-executor` agent for implementation
4. Mark task complete before proceeding
5. Never automatically move to next task
6. Wait for user instruction between tasks

## Critical Workflow Rules

### Universal Requirements

- **One spec at a time**: Never work on multiple specs simultaneously
- **Explicit approvals**: Wait for user confirmation between all phases
- **No phase skipping**: Must complete Requirements → Design → Tasks → Implementation sequence
- **Atomic execution**: Only one task at a time during implementation
- **Template adherence**: Follow exact template structures
- **Requirement traceability**: All tasks must reference specific requirements
- **Context loading**: Use hierarchical context loading at phase start, don't reload unnecessarily

### Approval Protocol

- Accept only clear affirmatives: "yes", "approved", "looks good"
- If user provides feedback, revise and re-request approval
- Never proceed without explicit approval
- Present validated outputs after agent validation passes

### Task Execution Protocol

1. Mark task as `in_progress` before starting
2. Implement following project conventions
3. Mark task as `complete` using `--mode complete` flag
4. Confirm completion to user
5. Stop and wait for next instruction
6. Never chain tasks automatically

### Naming Conventions

- Feature names: **kebab-case** (e.g., `user-authentication`)
- Bug names: **kebab-case** (e.g., `login-timeout-error`)
- File references: Use exact paths from templates

## Bug Fix Workflow

### Phase 1: Report Creation (`/bug-create`)

- Document observed behavior, expected behavior, reproduction steps
- Include environment details and error messages
- Use bug report template structure

### Phase 2: Analysis (`/bug-analyze`)

- Investigate root cause through codebase analysis
- Create detailed analysis.md with implementation plan
- Identify files to modify and changes required
- Get user approval before proceeding

### Phase 3: Fix Implementation (`/bug-fix`)

- Implement minimal, targeted changes
- Follow implementation plan from analysis.md
- Add regression tests
- Verify fix resolves original issue

### Phase 4: Verification (`/bug-verify`)

- Test original bug reproduction steps
- Verify no side effects or regressions
- Update documentation if needed
- Confirm fix completion

## Validation Agents

The workflow includes specialized agents that validate quality before user review:

- **spec-requirements-validator**: Checks requirements structure, EARS format, alignment with steering docs
- **spec-design-validator**: Validates technical soundness, architecture, leverage of existing code
- **spec-task-validator**: Ensures tasks are atomic, agent-friendly, properly scoped
- **spec-task-executor**: Implements individual tasks following project conventions

## Best Practices

### Codebase Research

Before each phase, perform mandatory analysis:
- Search for similar existing features
- Identify reusable components, utilities, patterns
- Review architecture and naming conventions
- Cross-reference with steering documents
- Document what can be reused vs. built new

### Code Quality

- Follow existing patterns over creating new ones
- Respect structure.md file organization
- Adhere to tech.md technical standards
- Make minimal, focused changes for bugs
- Include appropriate error handling and tests

### Context Management

- Load steering context once at workflow start
- Reference pre-loaded context throughout phases
- Don't reload templates unnecessarily
- Use `get-spec-context` to load phase documents
- Store and reuse gathered context

## Getting Started

1. **Setup Steering Documents**: Run `/spec-steering-setup` to create product.md, tech.md, structure.md
2. **Create Feature Spec**: Run `/spec-create {feature-name}` to start spec workflow
3. **Execute Tasks**: After planning, use `/spec-execute {task-id} {feature-name}` for implementation
4. **Track Progress**: Use `/spec-status {feature-name}` to monitor completion
5. **Report Bugs**: Use `/bug-create {bug-name}` to start bug workflow

## Notes

- This workflow system works with ANY codebase and technology stack
- Steering documents adapt to your specific project
- Templates provide consistent structure across all specifications
- The system enforces quality through validation agents
- All workflows emphasize traceability and documentation
