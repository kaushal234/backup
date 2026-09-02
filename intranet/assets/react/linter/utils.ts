import { execSync } from "child_process";
import fs from "fs";
import chalk from "chalk";
import { IBaseline } from "../types/IBaseline";
import { ILintItem } from "../types/ILintItem";
import {
  LINTER_BASELINE_FILENAME,
  LINTER_BASELINE_REPORT_FILENAME,
  LINTER_REPORT_FILENAME,
} from "../constants";
import { IMessage } from "../types/IMessage";

const generateLinterReport = (filename: string = LINTER_REPORT_FILENAME) => {
  const paths = [
    "assets/react/**/*.{js,jsx,ts,tsx}",
    "assets/controllers/**/*.{js,ts}",
    "assets/__tests__/**/*.{js,jsx,ts,tsx}",
  ];
  try {
    execSync(
      `eslint ${paths.map((p) => `"${p}"`).join(" ")} -f json -o ${filename}`,
      { stdio: "ignore" }
    );
    return true;
  } catch (err) {
    return false;
  }
};

const readLinterReport = (
  filename: string = LINTER_REPORT_FILENAME
): IBaseline => {
  if (!fs.existsSync(filename)) return { errorCount: 0, warningCount: 0 };

  const report: Array<ILintItem> = JSON.parse(
    fs.readFileSync(filename, "utf8")
  );

  let currentErrors = 0;
  let currentWarnings = 0;

  report.forEach((item) => {
    item.messages.forEach((message) => {
      if (message.ruleId !== "@typescript-eslint/no-explicit-any") {
        if (message.severity === 2) {
          currentErrors++;
        } else if (message.severity === 1) {
          currentWarnings++;
        }
      }
    });
  });

  return { errorCount: currentErrors, warningCount: currentWarnings };
};

export const getLinterReport = () => {
  generateLinterReport();
  return readLinterReport();
};

const readBaselineFile = (
  filename: string = LINTER_BASELINE_FILENAME
): IBaseline => {
  let baseline: IBaseline = { errorCount: 0, warningCount: 0 };
  if (fs.existsSync(filename)) {
    baseline = JSON.parse(fs.readFileSync(filename, "utf8"));
  }
  return baseline;
};

export const updateBaselineFile = (
  filename: string = LINTER_BASELINE_FILENAME
) => {
  const newBaseline = getLinterReport();
  generateLinterReport(LINTER_BASELINE_REPORT_FILENAME);

  fs.writeFileSync(filename, JSON.stringify(newBaseline, null, 2));

  console.info(
    `✅ Baseline updated to ${newBaseline.errorCount} errors, ${newBaseline.warningCount} warnings.`
  );
};

export const printMessage = (message: IMessage) => {
  const line = `${message.line}:${message.column}`.padEnd(8);
  const severity = chalk.red("error".padEnd(7));
  const rule = chalk.gray(message.ruleId || "");
  console.info(
    `  ${chalk.yellow(line)}  ${severity}  ${message.message}  ${rule}`
  );
};

export const printLintItem = (item: ILintItem) => {
  console.info(`\n${chalk.black(item.filePath)}`);
  item.messages.forEach((message) => printMessage(message));
};

export const diffBaseline = () => {
  const oldBaselineReport: Array<ILintItem> = JSON.parse(
    fs.readFileSync(LINTER_BASELINE_REPORT_FILENAME, "utf8")
  );

  const newBaselineReport: Array<ILintItem> = JSON.parse(
    fs.readFileSync(LINTER_REPORT_FILENAME, "utf8")
  );

  const oldBaselineReportMap = new Map<string, ILintItem>();
  oldBaselineReport.forEach((item) => {
    oldBaselineReportMap.set(item.filePath, item);
  });

  const diffReport: Array<ILintItem> = [];

  newBaselineReport.forEach((newItem) => {
    if (newItem.errorCount === 0 && newItem.warningCount === 0) return;

    const oldMessageMap = new Map<string, Array<IMessage>>();

    const newMessageMap = new Map<string, Array<IMessage>>();

    const oldItem = oldBaselineReportMap.get(newItem.filePath);

    oldItem?.messages.forEach((oldMsg) => {
      if (oldMsg.ruleId !== "@typescript-eslint/no-explicit-any") {
        if (oldMessageMap.has(oldMsg.ruleId)) {
          oldMessageMap.get(oldMsg.ruleId)?.push(oldMsg);
        } else {
          oldMessageMap.set(oldMsg.ruleId, [oldMsg]);
        }
      }
    });

    newItem.messages.forEach((newMsg) => {
      if (newMsg.ruleId !== "@typescript-eslint/no-explicit-any") {
        if (newMessageMap.has(newMsg.ruleId)) {
          newMessageMap.get(newMsg.ruleId)?.push(newMsg);
        } else {
          newMessageMap.set(newMsg.ruleId, [newMsg]);
        }
      }
    });

    const newMessages: Array<IMessage> = [];

    newMessageMap.forEach((value, key) => {
      if (oldMessageMap.get(key)?.length === value.length) return;
      newMessages.push(...value);
    });

    if (newMessages.length) {
      diffReport.push({ ...newItem, messages: newMessages });
    }
  });

  diffReport.forEach((item) => printLintItem(item));

  return diffReport;
};

const formatBaslineComparison = (
  oldBaseline: IBaseline,
  newBaseline: IBaseline
) => {
  return `\nBaseline: ${oldBaseline.errorCount} errors, ${oldBaseline.warningCount} warnings.\nCurrent: ${newBaseline.errorCount} errors, ${newBaseline.warningCount} warnings.`;
};

export const compareBaseline = (isPipeline: boolean) => {
  const newBaseline = getLinterReport();
  const oldBaseline = readBaselineFile();

  const diffReport = diffBaseline();

  const formattedMessage = formatBaslineComparison(oldBaseline, newBaseline);

  if (
    newBaseline.errorCount > oldBaseline.errorCount ||
    newBaseline.warningCount > oldBaseline.warningCount
  ) {
    console.error(`❌ ESLint issues increased!${formattedMessage}`);
    process.exit(1);
  }

  if (diffReport.length) {
    console.error(`❌ New ESLint issues found!`);
    process.exit(1);
  }

  if (
    newBaseline.errorCount === oldBaseline.errorCount &&
    newBaseline.warningCount === oldBaseline.warningCount
  ) {
    console.info(`✅ ESLint issues within baseline.`);
    return;
  }

  if (isPipeline) {
    console.error(`❌ Please update your baseline!${formattedMessage}`);
    process.exit(1);
  }

  generateLinterReport(LINTER_BASELINE_REPORT_FILENAME);
  fs.writeFileSync(
    LINTER_BASELINE_FILENAME,
    JSON.stringify(newBaseline, null, 2)
  );
  console.info(
    `✅ ESLint issues decreased!\nBaseline updated to ${newBaseline.errorCount} errors, ${newBaseline.warningCount} warnings.`
  );
};
